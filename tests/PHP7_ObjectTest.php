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
class PHP7_1_ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up test cases loading required classes.
     *
     * This is in setUp and not at the top of this file to ensure this is only
     * executed with PHP 7.1 (due to the `@requires` tag).
     */
    protected function setUp()
    {
       require_once 'JsonMapperTest/PlainObject.php';
       require_once 'JsonMapperTest/PHP7_Object.php';
    }
    /**
     * Test for PHP7 nullable types like "?Object"
     */
    public function testObjectSetterTypeNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typeNullableObject":null}'),
            new JsonMapperTest_PHP7_Object()
        );
        $this->assertNull($sn->typeNullableObject);
    }

    /**
     * Test for non-nullable types like "@param object" with null value
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "nonNullableObject" in class "JsonMapperTest_PHP7_Object" must not be NULL
     */
    public function testObjectSetterDocblockInvalidNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"nonNullableObject":null}'),
            new JsonMapperTest_PHP7_Object()
        );
    }
}
?>
