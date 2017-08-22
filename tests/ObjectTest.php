<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
require_once 'JsonMapperTest/Simple.php';
require_once 'JsonMapperTest/Object.php';
require_once 'JsonMapperTest/PlainObject.php';
require_once 'JsonMapperTest/ValueObject.php';
require_once 'JsonMapperTest/ComplexObject.php';

/**
 * Unit tests for JsonMapper's object handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for a class name "@var Classname"
     */
    public function testMapObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simple":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->simple);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->simple);
        $this->assertEquals('stringvalue', $sn->simple->str);
    }

    public function testMapDateTime()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":"2014-04-01T00:00:00+02:00"}'),
            new JsonMapperTest_Object()
        );
        $this->assertInstanceOf('DateTime', $sn->datetime);
        $this->assertEquals(
            '2014-04-01T00:00:00+02:00',
            $sn->datetime->format('c')
        );
    }

    public function testMapDateTimeNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->datetime);
    }

    public function testSettingValueObjects()
    {
        $valueObject = new JsonMapperTest_ValueObject('test');
        $jm = new JsonMapper();
        $sn = $jm->map(
            (object) array('value_object' => $valueObject),
            new JsonMapperTest_Simple()
        );

        $this->assertSame($valueObject, $sn->getValueObject());
    }

    public function testComplexObject()
    {
        $valueObject = new JsonMapperTest_ValueObject('test');
        $complexObject = new JsonMapperTest_ComplexObject($valueObject);
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(json_encode($complexObject)),
            (new ReflectionClass(JsonMapperTest_ComplexObject::class))->newInstanceWithoutConstructor()
        );

        $this->assertEquals(
            $complexObject->valueObject->getPublicValue(),
            $sn->valueObject->getPublicValue()
        );
    }

    public function testStrictTypeCheckingObject()
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;
        $sn = $jm->map(
            json_decode('{"pPlainObject":{"pStr":"abc"}}'),
            new JsonMapperTest_Object()
        );

        $this->assertInternalType('object', $sn->pPlainObject);
        $this->assertInstanceOf('JsonMapperTest_PlainObject', $sn->pPlainObject);
        $this->assertEquals('abc', $sn->pPlainObject->pStr);
    }

    /**
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "{}->pValueObject" has not the expected type \JsonMapperTest_ValueObject in object of type JsonMapperTest_Object: JSON value at {}->pValueObject must be an object, string given
     */
    public function testStrictTypeCheckingObjectError()
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;
        $sn = $jm->map(
            json_decode('{"pValueObject":"abc"}'),
            new JsonMapperTest_Object()
        );
    }

    /**
     * Test for "@var object|null" with null value
     */
    public function testObjectNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pValueObjectNullable":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->pValueObjectNullable);
    }

    /**
     * Test for "Object $obj = null" with null value
     */
    public function testObjectSetterNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"nullableObject":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->nullableObject);
    }

    /**
     * Test for "@param object|null" with null value
     */
    public function testObjectSetterDockblockNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"docblockNullableObject":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->docblockNullableObject);
    }

    /**
     * Test for "@var object" with null value
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "{}->pValueObject" has not the expected type \JsonMapperTest_ValueObject in object of type JsonMapperTest_Object: JSON value at {}->pValueObject must not be NULL
     */
    public function testObjectInvalidNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pValueObject":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertInternalType('null', $sn->pValueObjectNullable);
    }

    /**
     * Test for "@var object"
     */
    public function testMapObjectPropertyWithAnyObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"anyObject":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->anyObject);
        $this->assertInstanceOf('stdClass', $sn->anyObject);
        $this->assertEquals('stringvalue', $sn->anyObject->str);
    }

    public function testClassMap()
    {
        $jm = new JsonMapper();
        $jm->classMap['JsonMapperTest_PlainObject'] = 'DateTime';
        $sn = $jm->map(
            json_decode('{"pPlainObject":"2016-04-14T23:15:42+02:00"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInternalType('object', $sn->pPlainObject);
        $this->assertInstanceOf('DateTime', $sn->pPlainObject);
        $this->assertEquals(
            '2016-04-14T23:15:42+02:00',
            $sn->pPlainObject->format('c')
        );
    }

    public function testClassMapWithNamespace()
    {
        $jm = new JsonMapper();
        $jm->classMap['\\JsonMapperTest_PlainObject'] = 'DateTime';
        $sn = $jm->map(
            json_decode('{"pPlainObject":"2016-04-14T23:15:42+02:00"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInternalType('object', $sn->pPlainObject);
        $this->assertInstanceOf('DateTime', $sn->pPlainObject);
        $this->assertEquals(
            '2016-04-14T23:15:42+02:00',
            $sn->pPlainObject->format('c')
        );
    }

    public function testCompound1() {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"compound":"hello"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
        $this->assertEquals("hello", $sn->compound);
    }

    public function testCompound2() {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"compound":123}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
        $this->assertEquals(123, $sn->compound);
    }

    public function testCompound3() {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"compound":{"pStr":"hello"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
        $this->assertInstanceOf('JsonMapperTest_PlainObject', $sn->compound);
        $this->assertEquals("hello", $sn->compound->pStr);
    }

    public function testCompound4() {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"compound":{"pValueObject":{"publicValue":"hello"}}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
        $this->assertInstanceOf('JsonMapperTest_Object', $sn->compound);
        $this->assertInstanceOf('JsonMapperTest_ValueObject', $sn->compound->pValueObject);
        $this->assertEquals("hello", $sn->compound->pValueObject->publicValue);
    }

    /**
     * Test for compound with a property with the good name but a bad type
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage Error for JSON property "{}->compound" for class JsonMapperTest_Simple: Error for JSON property "{}->compound->pPlainObject" for class JsonMapperTest_Object: JSON property "{}->compound->pPlainObject->foo" does not exist in object of type JsonMapperTest_PlainObject
     */
    public function testCompoundGoodPropertyNameBadType() {
        $jm = new JsonMapper();
        $jm->map(
            json_decode('{"compound":{"pPlainObject":{ "foo":54}}}'),
            new JsonMapperTest_Simple()
        );
    }

}
?>
