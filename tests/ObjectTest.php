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

/**
 * Unit tests for JsonMapper's object handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ObjectTest extends \PHPUnit\Framework\TestCase
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
        $this->assertIsObject($sn->simple);
        $this->assertInstanceOf(JsonMapperTest_Simple::class, $sn->simple);
        $this->assertSame('stringvalue', $sn->simple->str);
    }

    public function testMapObjectByClassName()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simple":{"str":"stringvalue"}}'),
            JsonMapperTest_Simple::class
        );
        $this->assertIsObject($sn->simple);
        $this->assertInstanceOf(JsonMapperTest_Simple::class, $sn->simple);
        $this->assertSame('stringvalue', $sn->simple->str);
    }

    public function testMapDateTime()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":"2014-04-01T00:00:00+02:00"}'),
            new JsonMapperTest_Object()
        );
        $this->assertInstanceOf(DateTime::class, $sn->datetime);
        $this->assertSame(
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

        $this->assertIsObject($sn->pPlainObject);
        $this->assertInstanceOf(JsonMapperTest_PlainObject::class, $sn->pPlainObject);
        $this->assertSame('abc', $sn->pPlainObject->pStr);
    }

    public function testStrictTypeCheckingObjectError()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pValueObject" must be an object, string given');
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
     */
    public function testObjectInvalidNull()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pValueObject" in class "JsonMapperTest_Object" must not be NULL');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pValueObject":null}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->pValueObjectNullable);
    }

    /**
     * Test for "@var string" with object value
     */
    public function testObjectInsteadOfString()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pString" in class "JsonMapperTest_Object" is of type object and cannot be converted to string');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pString":{"key":"val"}}'),
            new JsonMapperTest_Object()
        );
        $this->assertNull($sn->pValueObjectNullable);
    }

    public function testConstructorWithoutParams()
    {
        $jm = new JsonMapper();
        $json = '[{"id":1}]';
        $objs = $jm->mapArray(
            json_decode($json),
            [],
            JsonMapperTest_ObjectConstructor::class
        );

        $this->assertSame('bar', $objs[0]->foo);
        $this->assertSame(1, $objs[0]->id);
    }

    public function testConstructorWithOptionalParams()
    {
        $jm = new JsonMapper();
        $json = '[{"id":1}]';
        $objs = $jm->mapArray(
            json_decode($json),
            [],
            JsonMapperTest_ObjectConstructorOptional::class
        );

        $this->assertSame('optional', $objs[0]->foo);
        $this->assertSame(1, $objs[0]->id);
    }

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
