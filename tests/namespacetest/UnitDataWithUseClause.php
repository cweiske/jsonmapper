<?php
namespace namespacetest;

use namespacetest\model;
use namespacetest\model\User;
use namespacetest\model\UserList;
use othernamespace\Foo;
use Foo2;

/**
 * Class UnitDataWithUseClause
 *
 * @package namespacetest
 */
class UnitDataWithUseClause
{
    /**
     * @var \ArrayObject[Unit]
     */
    public $data;

    /**
     * @var Foo2
     */
    public $foo2;

    /**
     * @var Unit[]
     */
    public $units;

    /**
     * @var Unit[][]
     */
    public $unit_matrix;

    /**
     * @var int[][]
     */
    public $int_matrix;

    /**
     * @var User[][][][]
     */
    public $multidimensional_array;

    /**
     * @var string[]
     */
    public $messages;

    /**
     * @var User
     */
    public $user;

    /**
     * @var
     */
    public $empty;

    /**
     * @var UserList[model\User]
     */
    public $users;

    /**
     * @var model\MyArrayObject
     */
    public $aodata;

    public $internalData = array();


    /**
     * @param Foo $foo
     */
    public function setNamespacedTypeHint(Foo $foo)
    {
        $this->internalData['namespacedTypeHint'] = $foo;
    }
}
?>
