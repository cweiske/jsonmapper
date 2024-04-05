<?php
/**
 * Part of JsonMapper
 *
 * PHP version 7.1
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */

/**
 * Unit tests for JsonMapper's object handling using PHP 7.1 syntax
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 7.1
 */
class Object_PHP71_Test extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for PHP7 nullable types like "?Object"
     */
    public function testObjectSetterTypeNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typeNullableObject":null}'),
            new JsonMapperTest_PHP7Object()
        );
        $this->assertNull($sn->typeNullableObject);
    }

    /**
     * Test for non-nullable types like "@param object" with null value
     */
    public function testObjectSetterDocblockInvalidNull()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "nonNullableObject" in class "JsonMapperTest_PHP7Object" must not be NULL');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"nonNullableObject":null}'),
            new JsonMapperTest_PHP7Object()
        );
    }
}
?>
