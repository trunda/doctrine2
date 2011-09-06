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

use Doctrine\ORM\Query\QueryException;

/**
 * Set of exceptions helper methods for SQLFilter funcionality
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.1
 * @author  Jakub Truneček <jakub@trunecek.net>
 */
class FilterException extends QueryException
{
    /**
     * Exception to be thrown in case of the provided filter class is not child
     * of the abstract class <tt>Doctrine\ORM\Query\Filter\SQLFilter</tt>
     * 
     * @param  string $className Class name to expanded in the message
     * @return FilterException
     */
    public static function invalidFilterClassParent($className)
    {
        return new self("Class $className does not extends abstract class Doctrine\ORM\Query\Filter\SQLFilter");
    }
    
    /**
     * Exception to be thrown in case that for given name was not found any filter
     * 
     * @param  string $name Provided name to be expanded int exeption message
     * @return FilterException
     */
    public static function filterClassWithNameNotFound($name)
    {
        return new self("No filter class with name $name was found");
    }
    
    /**
     * Exception to be thrown in case that given class does not exist
     * 
     * @param  string $className Provided name to be expanded int exeption message
     * @return FilterException
     */
    public static function filterClassNotFound($className)
    {
        return new self("No filter class $className was found");
    }
}
