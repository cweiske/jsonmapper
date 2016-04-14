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
require_once 'JsonMapperTest/Array.php';
require_once 'JsonMapperTest/Broken.php';
require_once 'JsonMapperTest/Simple.php';

/**
 * Unit tests for JsonMapper's array handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ArrayTest extends \PHPUnit_Framework_TestCase
{
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
     * flat/simple json values (string)
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
     * Test for an array that is nullable - "@var string[]|null"
     */
    public function testNullableSimple()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"nullableSimpleArray":null}'),
            new JsonMapperTest_Array()
        );
        $this->assertNull($sn->nullableSimpleArray);
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
     * Test for an array of float "@var float[]"
     */
    public function testFlArrayKeyed()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"flArray":{"foo":1.23,"bar":3.14,"baz":2.048}}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->flArray);
        $this->assertEquals(3, count($sn->flArray));
        $this->assertTrue(is_float($sn->flArray['foo']));
        $this->assertTrue(is_float($sn->flArray['bar']));
        $this->assertTrue(is_float($sn->flArray['baz']));
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
     * Test for an array of strings - "@var array[string]"
     */
    public function testStrArrayV2()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strArrayV2":["str",false,2.048]}'),
            new JsonMapperTest_Array()
        );
        $this->assertInternalType('array', $sn->strArrayV2);
        $this->assertEquals(3, count($sn->strArrayV2));
        $this->assertInternalType('string', $sn->strArrayV2[0]);
        $this->assertInternalType('string', $sn->strArrayV2[1]);
        $this->assertInternalType('string', $sn->strArrayV2[2]);
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
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "flArray" must be an array, integer given
     */
    public function testInvalidArray()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"flArray": 4 }'),
            new JsonMapperTest_Array()
        );
    }

    /**
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "pArrayObject" must be an array, double given
     */
    public function testInvalidArrayObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pArrayObject": 4.2 }'),
            new JsonMapperTest_Array()
        );
    }

    /**
     * A nullable ArrayObject which is null.
     */
    public function testArrayObjectNullable()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pNullableArrayObject": null}'),
            new JsonMapperTest_Array()
        );
        $this->assertNull($sn->pNullableArrayObject);
    }

    /**
     * An ArrayObject which may not be null but is.
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage JSON property "pArrayObject" must not be NULL
     */
    public function testArrayObjectInvalidNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pArrayObject": null}'),
            new JsonMapperTest_Array()
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
}
?>
