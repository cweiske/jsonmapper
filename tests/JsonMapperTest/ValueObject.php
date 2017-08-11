<?php

class JsonMapperTest_ValueObject
{
    public $publicValue;
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
        $this->publicValue = $value;
    }

    public function getPublicValue()
    {
        return $this->publicValue;
    }
}
