<?php
class JsonMapperTest_ComplexObject
{
    /**
     * @var JsonMapperTest_ValueObject
     */
    public $valueObject;

    /**
     * JsonMapperTest_ComplexObject constructor.
     * @param JsonMapperTest_ValueObject $valueObject
     */
    public function __construct(JsonMapperTest_ValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }
}
