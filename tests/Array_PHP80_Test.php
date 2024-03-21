<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for JsonMapper's support for PHP 8.0 typed arrays for properties with constructor promotion
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Andreas Wunderwald <wundii@gmail.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 8.0
 */
class Array_PHP80_Test extends TestCase
{
    public function testJsonMapper()
    {
        $json = json_decode('{"files": [{"value":"test.txt"}]}');
        $jsonMapper = new \JsonMapper();
        $jsonMapper->bIgnoreVisibility = true;
        $array = $jsonMapper->map($json, JsonMapperTest_PHP80Array::class);
        $this->assertCount(1, $array->getFiles());
        $this->assertInstanceOf(JsonMapperTest_ArrayValueForStringProperty::class, $array->getFiles()[0]);
    }
}
