<?php

class JsonMapperTest_PHP7_Object
{
    public function setTypeNullableObject(?JsonMapperTest_PlainObject $obj)
    {
        $this->typeNullableObject = $obj;
    }

    /**
     * @param JsonMapperTest_PlainObject $obj
     */
    public function setNonNullableObject($obj)
    {
        $this->nonNullableObject = $obj;
    }
}
?>
