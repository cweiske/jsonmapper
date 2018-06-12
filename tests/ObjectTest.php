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
     * @expectedExceptionMessage JSON property "pValueObject" must be an object, string given
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
     * @expectedExceptionMessage JSON property "pValueObject" in class "JsonMapperTest_Object" must not be NULL
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
     * Test for "@var string" with object value
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "pString" in class "JsonMapperTest_Object" is an object and cannot be converted to a string
     */
    public function testObjectInsteadOfString()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pString":{"key":"val"}}'),
            new JsonMapperTest_Object()
        );
        $this->assertInternalType('null', $sn->pValueObjectNullable);
    }

    /**
     * Abuse self
     */
    public function __invoke($class, $jvalue)
    {
        $testCase = $this;

        // the class/interface to be mapped
        $testCase->assertEquals($testCase::CLASS_MAP_CLASS, $class);
        $testCase->assertEquals($testCase::CLASS_MAP_DATA, $jvalue);

        return 'DateTime';
    }

    const CLASS_MAP_CLASS = 'JsonMapperTest_PlainObject';
    const CLASS_MAP_DATA = '2016-04-14T23:15:42+02:00';

    public function classMapTestData()
    {
        $testCase = $this;

        // classMap value
        return [
            'name' =>     ['DateTime'],
            'function' => [function ($class, $jvalue) use ($testCase) {
                // the class/interface to be mapped
                $testCase->assertEquals($testCase::CLASS_MAP_CLASS, $class);
                $testCase->assertEquals($testCase::CLASS_MAP_DATA, $jvalue);
                return 'DateTime';
            }],
            'invoke' =>   [$this],  // __invoke
        ];
    }

    /**
     * @dataProvider classMapTestData
     */
    public function testClassMap($classMapValue)
    {
        $jm = new JsonMapper();
        $jm->classMap[self::CLASS_MAP_CLASS] = $classMapValue;
        $sn = $jm->map(
            json_decode('{"pPlainObject":"'.self::CLASS_MAP_DATA.'"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInternalType('object', $sn->pPlainObject);
        $this->assertInstanceOf('DateTime', $sn->pPlainObject);
        $this->assertEquals(
            self::CLASS_MAP_DATA,
            $sn->pPlainObject->format('c')
        );
    }
}
?>
