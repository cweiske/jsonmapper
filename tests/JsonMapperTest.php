<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
require_once 'JsonMapperTest/Array.php';
require_once 'JsonMapperTest/Broken.php';
require_once 'JsonMapperTest/DependencyInjector.php';
require_once 'JsonMapperTest/Simple.php';
require_once 'JsonMapperTest/Logger.php';
require_once 'JsonMapperTest/PrivateWithSetter.php';
require_once 'JsonMapperTest/ValueObject.php';

use apimatic\jsonmapper\JsonMapper;
use apimatic\jsonmapper\JsonMapperException;

/**
 * Unit tests for JsonMapper
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class JsonMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for "@var string"
     */
    public function testMapSimpleString()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"str":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->str);
        $this->assertEquals('stringvalue', $sn->str);
    }

    /**
     * Test for "@var float"
     */
    public function testMapSimpleFloat()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"fl":"1.2"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('float', $sn->fl);
        $this->assertEquals(1.2, $sn->fl);
    }

    /**
     * Test for "@var double"
     */
    public function testMapSimpleDouble()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"db":"1.2"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('float', $sn->db);
        $this->assertEquals(1.2, $sn->db);
    }

    /**
     * Test for "@var bool"
     */
    public function testMapSimpleBool()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pbool":"1"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('boolean', $sn->pbool);
        $this->assertEquals(true, $sn->pbool);
    }

    /**
     * Test for "@var boolean"
     */
    public function testMapSimpleBoolean()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pboolean":"0"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('boolean', $sn->pboolean);
        $this->assertEquals(false, $sn->pboolean);
    }

    /**
     * Test for "@var int"
     */
    public function testMapSimpleInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pint":"123"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pint);
        $this->assertEquals(123, $sn->pint);
    }

    /**
     * Test for "@var integer"
     */
    public function testMapSimpleInteger()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pinteger":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pinteger);
        $this->assertEquals(12345, $sn->pinteger);
    }

    /**
     * Test for "@var mixed"
     */
    public function testMapSimpleMixed()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"mixed":12345}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->mixed);
        $this->assertEquals('12345', $sn->mixed);

        $sn = $jm->map(
            json_decode('{"mixed":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->mixed);
        $this->assertEquals(12345, $sn->mixed);
    }

    /**
     * Test for "@var int|null" with int value
     */
    public function testMapSimpleNullableInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":0}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pnullable);
        $this->assertEquals(0, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with null value
     */
    public function testMapSimpleNullableNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('null', $sn->pnullable);
        $this->assertEquals(null, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with string value
     */
    public function testMapSimpleNullableWrong()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pnullable);
        $this->assertEquals(12345, $sn->pnullable);
    }

    /**
     * Test for variable with no @var annotation
     */
    public function testMapSimpleNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"notype":{"k":"v"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->notype);
        $this->assertEquals((object) array('k' => 'v'), $sn->notype);
    }

    /**
     * Variable with an underscore
     */
    public function testMapSimpleUnderscore()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score":"f"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->under_score);
        $this->assertEquals('f', $sn->under_score);
    }

    /**
     * Variable with an underscore and a setter method
     */
    public function testMapSimpleUnderscoreSetter()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score_setter":"blubb"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType(
            'string', $sn->internalData['under_score_setter']
        );
        $this->assertEquals(
            'blubb', $sn->internalData['under_score_setter']
        );
    }

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

    /**
     * Test for an array of classes "@var Classname[]"
     */
    public function testMapTypedArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typedArray":[{"str":"stringvalue"},{"fl":"1.2"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->typedArray);
        $this->assertEquals(2, count($sn->typedArray));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArray[0]);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->typedArray[1]);
        $this->assertEquals('stringvalue', $sn->typedArray[0]->str);
        $this->assertEquals(1.2, $sn->typedArray[1]->fl);
    }

    /**
     * Test for an array of classes "@var ClassName[]" with
     * flat/simple json values (string, float)
     */
    public function testMapTypedSimpleArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typedSimpleArray":["2014-01-02",null,"2014-05-07"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->typedSimpleArray);
        $this->assertEquals(3, count($sn->typedSimpleArray));
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[0]);
        $this->assertNull($sn->typedSimpleArray[1]);
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[2]);
        $this->assertEquals(
            '2014-01-02', $sn->typedSimpleArray[0]->format('Y-m-d')
        );
        $this->assertEquals(
            '2014-05-07', $sn->typedSimpleArray[2]->format('Y-m-d')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage JsonMapper::map() requires first argument to be an object, NULL given.
     */
    public function testMapNullJson()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(null, new JsonMapperTest_Simple());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage JsonMapper::map() requires second argument to be an object, NULL given.
     */
    public function testMapNullObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(new stdClass(), null);
    }

    public function testMapArrayJsonNoTypeEnforcement()
    {
        $jm = new JsonMapper();
        $jm->bEnforceMapType = false;
        $sn = $jm->map(array(), new JsonMapperTest_Simple());
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn);
    }

    /**
     * Test for an array of float "@var float[]"
     */
    public function testFlArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"flArray":[1.23,3.14,2.048]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->flArray);
        $this->assertEquals(3, count($sn->flArray));
        $this->assertTrue(is_float($sn->flArray[0]));
        $this->assertTrue(is_float($sn->flArray[1]));
        $this->assertTrue(is_float($sn->flArray[2]));
    }

    /**
     * Test for an array of strings - "@var string[]"
     */
    public function testStrArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strArray":["str",false,2.048]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->strArray);
        $this->assertEquals(3, count($sn->strArray));
        $this->assertInternalType('string', $sn->strArray[0]);
        $this->assertInternalType('string', $sn->strArray[1]);
        $this->assertInternalType('string', $sn->strArray[2]);
    }

    /**
     * Test for "@var ArrayObject"
     */
    public function testMapArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pArrayObject":[{"str":"stringvalue"},{"fl":"1.2"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pArrayObject);
        $this->assertEquals(2, count($sn->pArrayObject));
        $this->assertInstanceOf('\stdClass', $sn->pArrayObject[0]);
        $this->assertInstanceOf('\stdClass', $sn->pArrayObject[1]);
        $this->assertEquals('stringvalue', $sn->pArrayObject[0]->str);
        $this->assertEquals('1.2', $sn->pArrayObject[1]->fl);
    }

    /**
     * Test for "@var ArrayObject[Classname]"
     */
    public function testMapTypedArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pTypedArrayObject":[{"str":"stringvalue"},{"fl":"1.2"}]}'
            ),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pTypedArrayObject);
        $this->assertEquals(2, count($sn->pTypedArrayObject));
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->pTypedArrayObject[0]);
        $this->assertInstanceOf('JsonMapperTest_Simple', $sn->pTypedArrayObject[1]);
        $this->assertEquals('stringvalue', $sn->pTypedArrayObject[0]->str);
        $this->assertEquals('1.2', $sn->pTypedArrayObject[1]->fl);
    }

    /**
     * Test for "@var ArrayObject[int]"
     */
    public function testMapSimpleArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pSimpleArrayObject":{"eins":"1","zwei":"1.2"}}'
            ),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pSimpleArrayObject);
        $this->assertEquals(2, count($sn->pSimpleArrayObject));
        $this->assertInternalType('int', $sn->pSimpleArrayObject['eins']);
        $this->assertInternalType('int', $sn->pSimpleArrayObject['zwei']);
        $this->assertEquals(1, $sn->pSimpleArrayObject['eins']);
        $this->assertEquals(1, $sn->pSimpleArrayObject['zwei']);
    }

    /**
     * Test for "@var "
     *
     * @expectedException apimatic\jsonmapper\JsonMapperException
     * @expectedExceptionMessage Empty type at property "JsonMapperTest_Simple::$empty"
     */
    public function testMapEmpty()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"empty":{"a":"b"}}'
            ),
            new JsonMapperTest_Simple()
        );
    }

    /**
     * The TYPO3 autoloader breaks if we autoload a class with a [ or ]
     * in its name.
     *
     * @runInSeparateProcess
     */
    public function testMapTypedArrayObjectDoesNotExist()
    {
        $this->assertTrue(
            spl_autoload_register(
                array($this, 'mapTypedArrayObjectDoesNotExistAutoloader')
            )
        );
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pTypedArrayObjectNoClass":[{"str":"stringvalue"}]}'
            ),
            new JsonMapperTest_Broken()
        );
        $this->assertInstanceOf('ArrayObject', $sn->pTypedArrayObjectNoClass);
        $this->assertEquals(1, count($sn->pTypedArrayObjectNoClass));
        $this->assertInstanceOf(
            'ThisClassDoesNotExist', $sn->pTypedArrayObjectNoClass[0]
        );
    }

    public function mapTypedArrayObjectDoesNotExistAutoloader($class)
    {
        $this->assertFalse(
            strpos($class, '['),
            'class name contains a "[": ' . $class
        );
        $code = '';
        if (strpos($class, '\\') !== false) {
            $lpos = strrpos($class, '\\');
            $namespace = substr($class, 0, $lpos);
            $class = substr($class, $lpos + 1);
            $code .= 'namespace ' . $namespace . ";\n";
        }
        $code .= 'class ' . $class . '{}';
        eval($code);
    }

    /**
     * There is no property, but a setter method.
     * The parameter has a type hint.
     */
    public function testMapOnlySetterTypeHint()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyTypeHint":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );

        $this->assertInternalType('object', $sn->internalData['typehint']);
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['typehint']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['typehint']->str
        );
    }

    /**
     * There is no property, but a setter method.
     * It indicates the type in the docblock's @param annotation
     */
    public function testMapOnlySetterDocblock()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyDocblock":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->internalData['docblock']);
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['docblock']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['docblock']->str
        );
    }

    /**
     * There is no property, but a setter method, but it indicates no type
     */
    public function testMapOnlySetterNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyNoType":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->internalData['notype']);
        $this->assertInstanceOf(
            'stdClass', $sn->internalData['notype']
        );
        $this->assertEquals(
            'stringvalue', $sn->internalData['notype']->str
        );
    }

    /**
     * Test for protected properties that have no setter method
     */
    public function testMapProtectedWithoutSetterMethod()
    {
        $jm = new JsonMapper();
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);
        $sn = $jm->map(
            json_decode('{"protectedStrNoSetter":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('null', $sn->getProtectedStrNoSetter());
        $this->assertEquals(
            array(
                array(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array(
                        'class' => 'JsonMapperTest_Simple',
                        'property' => 'protectedStrNoSetter'
                    )
                )
            ),
            $logger->log
        );
    }

    public function testMapDateTime()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"datetime":"2014-04-01T00:00:00+02:00"}'),
            new JsonMapperTest_Simple()
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
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->datetime);
    }

    /**
     * @expectedException        apimatic\jsonmapper\JsonMapperException
     * @expectedExceptionMessage Required property "pMissingData" of class JsonMapperTest_Broken is missing in JSON data
     */
    public function testMissingDataException()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{}'),
            new JsonMapperTest_Broken()
        );
    }

    /**
     * We check that checkMissingData exits cleanly; needed for 100% coverage.
     */
    public function testMissingDataNoException()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{"pMissingData":1}'),
            new JsonMapperTest_Broken()
        );
        $this->assertTrue(true);
    }

    /**
     * @expectedException        apimatic\jsonmapper\JsonMapperException
     * @expectedExceptionMessage JSON property "undefinedProperty" does not exist in object of type JsonMapperTest_Broken
     */
    public function testUndefinedPropertyException()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $sn = $jm->map(
            json_decode('{"undefinedProperty":123}'),
            new JsonMapperTest_Broken()
        );
    }

    public function testPrivatePropertyWithPublicSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateProperty" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    /**
     * @expectedException        apimatic\jsonmapper\JsonMapperException
     * @expectedExceptionMessage JSON property "privateNoSetter" has no public setter method in object of type PrivateWithSetter
     */
    public function testPrivatePropertyWithNoSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    /**
     * @expectedException        apimatic\jsonmapper\JsonMapperException
     * @expectedExceptionMessage JSON property "privatePropertyPrivateSetter" has no public setter method in object of type PrivateWithSetter
     */
    public function testPrivatePropertyWithPrivateSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privatePropertyPrivateSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());
    }

    public function testSetterIsPreferredOverProperty()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"setterPreferredOverProperty":"foo"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->setterPreferredOverProperty);
        $this->assertEquals(
            'set via setter: foo', $sn->setterPreferredOverProperty
        );
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

    public function testCaseInsensitivePropertyMatching()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            (object) array('PINT' => 2),
            new JsonMapperTest_Simple()
        );

        $this->assertSame(2, $sn->pint);
    }

    public function testDependencyInjection()
    {
        $jm = new JsonMapperTest_DependencyInjector();

        $sn = $jm->map(
            (object) array(
                'str' => 'first level',
                'simple' => (object) array(
                    'str' => 'second level'
                )
            ),
            $jm->createInstance('JsonMapperTest_Simple')
        );

        $this->assertEquals('first level', $sn->str);
        $this->assertEquals('database', $sn->db);

        $this->assertEquals('second level', $sn->simple->str);
        $this->assertEquals('database', $sn->simple->db);
    }
}
?>
