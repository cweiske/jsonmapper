<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for JsonMapper's support for PHP 7.4 typed arrays
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Martin Reinfandt <martin.reinfandt@check24.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 7.4
 */
class Array_PHP74_Test extends TestCase
{
    public function testJsonMapper()
    {
        $json = json_decode('{"files": ["test.txt"]}');
        $jsonMapper = new \JsonMapper();
        $array = $jsonMapper->map($json, new JsonMapperTest_PHP74Array());
        $this->assertCount(1, $array->files);
    }

    public function testMapArrayValueToStringProperty()
    {

        $jm = new JsonMapper();
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "value" in class "JsonMapperTest_ArrayValueForStringProperty" is of type array and cannot be converted to string');
        $jm->map(
            json_decode('{"value":[]}'),
            new JsonMapperTest_ArrayValueForStringProperty()
        );
    }
}
