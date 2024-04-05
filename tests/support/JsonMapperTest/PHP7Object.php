<?php

declare(strict_types=1);

class JsonMapperTest_PHP7Object
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
