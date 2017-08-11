<?php
/**
 * Created by PhpStorm.
 * User: jaredchu
 * Date: 11/08/2017
 * Time: 16:39
 */
require_once __DIR__ . '/ValueObject.php';

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