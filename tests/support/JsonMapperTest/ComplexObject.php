<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: jaredchu
 * Date: 11/08/2017
 * Time: 16:39
 */

class JsonMapperTest_ComplexObject
{
    /**
     * @var JsonMapperTest_ValueObject
     */
    public $valueObject;

    public function __construct(JsonMapperTest_ValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }
}
