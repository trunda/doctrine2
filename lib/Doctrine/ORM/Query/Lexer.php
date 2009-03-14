<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

namespace Doctrine\ORM\Query;

/**
 * Scans a DQL query for tokens.
 *
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Janne Vanhala <jpvanhal@cc.hut.fi>
 * @author      Roman Borschel <roman@code-factory.org>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       2.0
 * @version     $Revision$
 */
class Lexer
{
    /**
     * Array of scanned tokens.
     *
     * @var array
     */
    private $_tokens = array();

    /**
     * @todo Doc
     */
    private $_position = 0;

    /**
     * @todo Doc
     */
    private $_peek = 0;

    /**
     * @var array The next token in the query string.
     */
    public $lookahead;

    /**
     * @var array The last matched token.
     */
    public $token;

    /**
     * Creates a new query scanner object.
     *
     * @param string $input a query string
     */
    public function __construct($input)
    {
        $this->_scan($input);
    }

    /**
     * Checks whether a given token matches the current lookahead.
     *
     * @param <type> $token
     * @return <type>
     */
    public function isNextToken($token)
    {
        $la = $this->lookahead;
        return ($la['type'] === $token || $la['value'] === $token);
    }

    /**
     * Moves to the next token in the input string.
     *
     * A token is an associative array containing three items:
     *  - 'value'    : the string value of the token in the input string
     *  - 'type'     : the type of the token (identifier, numeric, string, input
     *                 parameter, none)
     *  - 'position' : the position of the token in the input string
     *
     * @return array|null the next token; null if there is no more tokens left
     */
    public function next()
    {
        $this->token = $this->lookahead;
        $this->_peek = 0;
        if (isset($this->_tokens[$this->_position])) {
            $this->lookahead = $this->_tokens[$this->_position++];
        } else {
            $this->lookahead = null;
        }
    }

    /**
     * Returns the next token in the input string.
     *
     * A token is an associative array containing three items:
     *  - 'value'    : the string value of the token in the input string
     *  - 'type'     : the type of the token (identifier, numeric, string, input
     *                 parameter, none)
     *  - 'position' : the position of the token in the input string
     *
     * @return array|null the next token; null if there is no more tokens left
     */
    /*public function next()
    {
        $this->_peek = 0;
        if (isset($this->_tokens[$this->_position])) {
            return $this->_tokens[$this->_position++];
        } else {
            return null;
        }
    }*/

    /**
     * Checks if an identifier is a keyword and returns its correct type.
     *
     * @param string $identifier identifier name
     * @return int token type
     */
    public function _checkLiteral($identifier)
    {
        $name = 'Doctrine\ORM\Query\Token::T_' . strtoupper($identifier);

        if (defined($name)) {
            $type = constant($name);

            if ($type > 100) {
                return $type;
            }
        }

        return Token::T_IDENTIFIER;
    }

    /**
     * Scans the input string for tokens.
     *
     * @param string $input a query string
     */
    protected function _scan($input)
    {
        static $regex;

        if ( ! isset($regex)) {
            $patterns = array(
                '[a-z_][a-z0-9_\\\]*',
                '(?:[0-9]+(?:[,\.][0-9]+)*)(?:e[+-]?[0-9]+)?',
                "'(?:[^']|'')*'",
                '\?[0-9]+|:[a-z][a-z0-9_]+'
            );
            $regex = '/(' . implode(')|(', $patterns) . ')|\s+|(.)/i';
        }

        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $matches = preg_split($regex, $input, -1, $flags);

        foreach ($matches as $match) {
            $value = $match[0];
            $type = $this->_getType($value);
            $this->_tokens[] = array(
                'value' => $value,
                'type'  => $type,
                'position' => $match[1]
            );
        }
    }

    /**
     * @todo Doc
     */
    protected function _getType(&$value)
    {
        // $value is referenced because it can be changed if it is numeric.
        // [TODO] Revisit the _isNumeric and _getNumeric methods to reduce overhead.
        $type = Token::T_NONE;

        $newVal = $this->_getNumeric($value);
        if ($newVal !== false){
            $value = $newVal;
            if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                $type = Token::T_FLOAT;
            } else {
                $type = Token::T_INTEGER;
            }
        }
        if ($value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $type = Token::T_STRING;
        } else if (ctype_alpha($value[0]) || $value[0] === '_') {
            $type = $this->_checkLiteral($value);
        } else if ($value[0] === '?' || $value[0] === ':') {
            $type = Token::T_INPUT_PARAMETER;
        }

        return $type;
    }

    /**
     * @todo Doc
     */
    protected function _getNumeric($value)
    {
        if ( ! is_scalar($value)) {
            return false;
        }
        // Checking for valid numeric numbers: 1.234, -1.234e-2
        if (is_numeric($value)) {
            return $value;
        }

        // World number: 1.000.000,02 or -1,234e-2
        $worldnum = strtr($value, array('.' => '', ',' => '.'));
        if (is_numeric($worldnum)) {
            return $worldnum;
        }

        // American extensive number: 1,000,000.02
        $american_en = strtr($value, array(',' => ''));
        if (is_numeric($american_en)) {
            return $american_en;
        }

        return false;

    }

    /**
     * @todo Doc
     */
    public function isA($value, $token)
    {
        $type = $this->_getType($value);

        return $type === $token;
    }

    /**
     * Moves the lookahead token forward.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function peek()
    {
        if (isset($this->_tokens[$this->_position + $this->_peek])) {
            return $this->_tokens[$this->_position + $this->_peek++];
        } else {
            return null;
        }
    }

    /**
     * Peeks at the next token, returns it and immediately resets the peek.
     *
     * @return array|null The next token or NULL if there are no more tokens ahead.
     */
    public function glimpse()
    {
        $peek = $this->peek();
        $this->_peek = 0;
        return $peek;
    }

    /**
     * @todo Doc
     */
    public function resetPeek()
    {
        $this->_peek = 0;
    }

    /**
     * @todo Doc
     */
    public function resetPosition($position = 0)
    {
        $this->_position = $position;
    }
}