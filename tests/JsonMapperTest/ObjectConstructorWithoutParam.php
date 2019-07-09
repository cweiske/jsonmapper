<?php


class JsonMapperTest_ObjectConstructorWithoutParam
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    private $rand;

    public function __construct()
    {
        $this->rand = mt_rand(1, 10);
    }

    public function getRand()
    {
        return $this->rand;
    }
}