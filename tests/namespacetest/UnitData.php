<?php
namespace namespacetest;

class UnitData
{
    /**
     * @var \ArrayObject[Unit]
     */
    public $data;

    /**
     * @var Unit[]
     */
    public $units;

    /**
     * @var string[]
     */
    public $messages;

    /**
     * @var model\User
     */
    public $user;

    /**
     * @var
     */
    public $empty;

    /**
     * @var model\UserList[model\User]
     */
    public $users;

    /**
     * @var model\MyArrayObject
     */
    public $aodata;

    public $internalData = array();


    public function setNamespacedTypeHint(\othernamespace\Foo $foo)
    {
        $this->internalData['namespacedTypeHint'] = $foo;
    }
}
?>
