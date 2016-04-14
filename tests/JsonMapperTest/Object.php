<?php

class JsonMapperTest_Object
{
    /**
     * @var \DateTime|null
     */
    public $datetime;

    /**
     * @var JsonMapperTest_ValueObject
     */
    public $pValueObject;

    /**
     * @var JsonMapperTest_ValueObject|null
     */
    public $pValueObjectNullable;

    /**
     * @var JsonMapperTest_PlainObject
     */
    public $pPlainObject;

    public function setNullableObject(JsonMapperTest_PlainObject $obj = null)
    {
        $this->nullableObject = $obj;
    }

    /**
     * @param JsonMapperTest_PlainObject|null $obj
     */
    public function setDocblockNullableObject($obj)
    {
        $this->docblockNullableObject = $obj;
    }
}
?>
