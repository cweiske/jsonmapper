<?php

declare(strict_types=1);
class JsonMapperTest_ObjectConstructor
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $foo;

    public function __construct()
    {
        $this->foo = 'bar';
    }
}
