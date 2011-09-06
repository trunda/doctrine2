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
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Query\Filter;

use Doctrine\DBAL\Connection,
    Doctrine\DBAL\Types\Type,
    Doctrine\ORM\Query\ParameterTypeInferer,
    Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This abstract class is used for defining global SQL filter.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.1
 * @author  Jakub Truneček <jakub@trunecek.net>
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class SQLFilter
{
    /** 
     * @var Connection 
     */
    private $_con;

    /** 
     * @var array 
     */
    private $_params = array();
    
    /** 
     * @var array 
     */
    private $_paramTypes = array();
    
    /**
     * Initilizes new <tt>SQLFilter</tt>
     * 
     * @param  Connection $connection The connection to use for quoting parameters
     */
    public final function __construct(Connection $connection)
    {
        $this->_con = $connection;
    }

    /**
     * Returns quoted value of parameter by  given name
     * For quoting is used connection provided in constructor
     * 
     * @param  string $name Name of the parameter to be returned
     * @return string Quoted parameter
     */
    final public function getParameter($name)
    {
        return $this->_con->quote($this->_params[$name], 
                Type::getType($this->_paramTypes[$name])->getBindingType());
    }
    
    /**
     * Sets the parameter value. Parameter can be used inside the filter (@see SQLFilter::addFilterConstraint)
     * method.
     * 
     * @param  string $name Name of the parameter
     * @param  mixed $value Value of the parameter
     * @param  mixed $type Type constant
     * @return SQLFilter 
     */
    final public function setParameter($name, $value, $type = null)
    {
        if ($type === null) {
            $type = ParameterTypeInferer::inferType($value);
        }
        
        $this->_paramTypes[$name] = $type;
        $this->_params[$name] = $value;
        
        return $this;        
    }
    
    /**
     * This method provides filter main functionality. Every child of this class
     * have to define it. Method is called by Persister and by SqlWalker to add
     * constraints to the final query
     * 
     * @param  ClassMetadata $targetEintity Metadata of the target entity
     * @param  string $targetTableAlias Alias of the table used in the query
     * @return string Constraint to be applied
     */
    abstract public function addFilterConstraint(ClassMetadata $targetEintity, $targetTableAlias);
}

