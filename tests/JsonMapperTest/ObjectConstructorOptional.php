<?php
class JsonMapperTest_ObjectConstructorOptional
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $foo;

    public function __construct($foo = 'optional')
    {
        $this->foo = $foo;
    }
}
