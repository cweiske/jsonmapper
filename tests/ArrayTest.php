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

use namespacetest\model\MyArrayObject;

require_once 'JsonMapperTest/Array.php';
require_once 'JsonMapperTest/ArrayAccessCollection.php';
require_once 'JsonMapperTest/Broken.php';
require_once 'JsonMapperTest/Simple.php';
require_once 'JsonMapperTest/VariadicArray.php';
require_once 'JsonMapperTest/Zoo/Animal.php';
require_once 'JsonMapperTest/Zoo/Zoo.php';
require_once 'JsonMapperTest/Zoo/Cat.php';
require_once 'JsonMapperTest/Zoo/Fish.php';

/**
 * Unit tests for JsonMapper's array handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ArrayTest extends \PHPUnit\Framework\TestCase
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
        $this->assertIsArray($sn->typedArray);
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
            json_decode('{"typedSimpleArray":["2014-01-02","2014-05-07"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->typedSimpleArray);
        $this->assertEquals(2, count($sn->typedSimpleArray));
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[0]);
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray[1]);
        $this->assertEquals(
            '2014-01-02', $sn->typedSimpleArray[0]->format('Y-m-d')
        );
        $this->assertEquals(
            '2014-05-07', $sn->typedSimpleArray[1]->format('Y-m-d')
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
        $this->assertIsArray($sn->flArray);
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
        $this->assertIsArray($sn->flArray);
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
            json_decode('{"strArray":["str",false,2.048,null]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->strArray);
        $this->assertEquals(4, count($sn->strArray));
        $this->assertIsString($sn->strArray[0]);
        $this->assertIsString($sn->strArray[1]);
        $this->assertIsString($sn->strArray[2]);
        $this->assertIsString($sn->strArray[3]);
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
        $this->assertIsArray($sn->strArrayV2);
        $this->assertEquals(3, count($sn->strArrayV2));
        $this->assertIsString($sn->strArrayV2[0]);
        $this->assertIsString($sn->strArrayV2[1]);
        $this->assertIsString($sn->strArrayV2[2]);
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
        $this->assertIsInt($sn->pSimpleArrayObject['eins']);
        $this->assertIsInt($sn->pSimpleArrayObject['zwei']);
        $this->assertEquals(1, $sn->pSimpleArrayObject['eins']);
        $this->assertEquals(1, $sn->pSimpleArrayObject['zwei']);
    }

    public function testMapSimpleArrayAccess()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"pArrayAccessCollection":{"eins": 1,"zwei": "two"}}'
            ),
            new JsonMapperTest_Array()
        );
        $this->assertInstanceOf('ArrayAccess', $sn->pArrayAccessCollection);
        $this->assertIsInt($sn->pArrayAccessCollection['eins']);
        $this->assertIsString($sn->pArrayAccessCollection['zwei']);
        $this->assertEquals(1, $sn->pArrayAccessCollection['eins']);
        $this->assertEquals("two", $sn->pArrayAccessCollection['zwei']);
    }

    public function testInvalidArray()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "flArray" must be an array, integer given');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"flArray": 4 }'),
            new JsonMapperTest_Array()
        );
    }

    public function testInvalidArrayObject()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pArrayObject" must be an array, double given');
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
     */
    public function testArrayObjectInvalidNull()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pArrayObject" in class "JsonMapperTest_Array" must not be NULL');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pArrayObject": null}'),
            new JsonMapperTest_Array()
        );
    }

    /**
     * Exception is not thrown when a non-nullable object has null value
     * but strict nullable checks are turned off
     */
    public function testNonNullableArrayObjectWithLooseNullChecks()
    {
        $jm = new JsonMapper();
        $jm->bStrictNullTypes = false;
        $sn = $jm->map(
            json_decode('{"pArrayObject": null}'),
            new JsonMapperTest_Array()
        );
        $this->assertNull($sn->pArrayObject);
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
     * Lists or ArrayObject instances.
     */
    public function testArrayObjectList()
    {
        $jm = new JsonMapper();
        $jm->bStrictNullTypes = false;
        $sn = $jm->map(
            json_decode('{"pArrayObjectList": [{"x":"X"},{"y":"Y"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertNotNull($sn->pArrayObjectList);
        $this->assertIsArray($sn->pArrayObjectList);
        $this->assertCount(2, $sn->pArrayObjectList);
        $this->assertContainsOnlyInstancesOf(\ArrayObject::class, $sn->pArrayObjectList);
        // test first element data
        $ao = $sn->pArrayObjectList[0];
        $this->assertEquals(['x' => 'X'], $ao->getArrayCopy());
    }

    /**
     * Lists or ArrayObject subclass instances.
     */
    public function testArrayObjectSubclassList()
    {
        $jm = new JsonMapper();
        $jm->bStrictNullTypes = false;
        $sn = $jm->map(
            json_decode('{"pArrayObjectSubclassList": [{"x":"X"},{"y":"Y"}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertNotNull($sn->pArrayObjectSubclassList);
        $this->assertIsArray($sn->pArrayObjectSubclassList);
        $this->assertCount(2, $sn->pArrayObjectSubclassList);
        $this->assertContainsOnlyInstancesOf(MyArrayObject::class, $sn->pArrayObjectSubclassList);
        // test first element data
        $ao = $sn->pArrayObjectSubclassList[0];
        $this->assertEquals(['x' => 'X'], $ao->getArrayCopy());
    }

    /**
     * Test for an array of array of integers "@var int[][]"
     */
    public function testNMatrix()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"nMatrix":[[1,2],[3,4],[5]]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->nMatrix);
        $this->assertEquals(3, count($sn->nMatrix));
        $this->assertIsArray($sn->nMatrix[0]);
        $this->assertIsArray($sn->nMatrix[1]);
        $this->assertIsArray($sn->nMatrix[2]);

        $this->assertEquals(2, count($sn->nMatrix[0]));
        $this->assertIsInt($sn->nMatrix[0][0]);
        $this->assertIsInt($sn->nMatrix[0][1]);

        $this->assertEquals(2, count($sn->nMatrix[1]));
        $this->assertEquals(1, count($sn->nMatrix[2]));
    }

    /**
     * Test for an array of arrays of arrays of objects
     * "@var JsonMapper_Simple[][][]"
     */
    public function testObjectMultiverse()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pMultiverse":[[[{"pint":23}]]]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->pMultiverse);
        $this->assertEquals(1, count($sn->pMultiverse));

        $this->assertIsArray($sn->pMultiverse[0]);
        $this->assertEquals(1, count($sn->pMultiverse[0]));

        $this->assertIsArray($sn->pMultiverse[0][0]);
        $this->assertEquals(1, count($sn->pMultiverse[0][0]));

        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->pMultiverse[0][0][0]
        );
    }

    /**
     * Dead simple mapArray test
     */
    public function testMapArray()
    {
        $jm = new JsonMapper();
        $mapped = $jm->mapArray(
            json_decode('[1,2,3]'),
            []
        );
        $this->assertEquals([1, 2, 3], $mapped);
    }

    /**
     * Make sure we're not modifying array keys
     * as we do with object names (getSafeName)
     */
    public function testMapArrayStrangeKeys()
    {
        $jm = new JsonMapper();
        $mapped = $jm->mapArray(
            ['en-US' => 'foo', 'de-DE' => 'bar'],
            []
        );
        $this->assertEquals(['en-US' => 'foo', 'de-DE' => 'bar'], $mapped);
    }

    /**
     * Map a JSON object to an array with a key that contains a hyphen.
     */
    public function testMapTypedSimpleArrayFromObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"typedSimpleArray":{"en-US":"2014-01-02"}}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->typedSimpleArray);
        $this->assertEquals(1, count($sn->typedSimpleArray));
        $this->assertArrayHasKey('en-US', $sn->typedSimpleArray);
        $this->assertInstanceOf('DateTime', $sn->typedSimpleArray['en-US']);
        $this->assertEquals(
            '2014-01-02', $sn->typedSimpleArray['en-US']->format('Y-m-d')
        );
    }

    /**
     * Test for "@var string[]" with object value
     */
    public function testObjectInsteadOfString()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "strArray" is an array of type "string" but contained a value of type "object"');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"strArray":[{}]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($sn->strArray);
        $this->assertNotEmpty($sn->strArray);
    }

    public function testPolymorphicArray()
    {
        $zooJson = <<<JSON
        {
            "animals": [
                {
                    "kind": "cat",
                    "name": "Lion"
                },
                {
                    "kind": "fish",
                    "name": "Clown Fish"
                }
            ]
        }
JSON;

        $jm = new JsonMapper();
        $jm->classMap[Animal::class] = function ($class, $jvalue) {
            return Animal::determineClass($class, $jvalue);
        };

        $zoo = $jm->map(json_decode($zooJson), new Zoo());
        $this->assertEquals(2, count($zoo->animals));

        $this->assertInstanceOf(Cat::class, $zoo->animals[0]);
        $this->assertEquals('Lion', $zoo->animals[0]->name);

        $this->assertInstanceOf(Fish::class, $zoo->animals[1]);
        $this->assertEquals('Clown Fish', $zoo->animals[1]->name);
    }

    public function testMapArrayFromVariadicFunctionWithSimpleType()
    {
        $jm = new JsonMapper();
        /** @var JsonMapperTest_VariadicArray $sn */
        $sn = $jm->map(
            json_decode('{"variadicInt":[1, 2, 3]}'),
            new JsonMapperTest_VariadicArray()
        );
        $variadicArray = $sn->getVariadicInt();

        $this->assertIsArray($variadicArray);
        $this->assertEquals(3, count($variadicArray));
        $this->assertIsInt($variadicArray[0]);
        $this->assertIsInt($variadicArray[1]);
        $this->assertIsInt($variadicArray[2]);
        $this->assertEquals(
            1, $variadicArray[0]
        );
        $this->assertEquals(
            2, $variadicArray[1]
        );
        $this->assertEquals(
            3, $variadicArray[2]
        );
    }

    public function testMapArrayFromVariadicFunctionWithObjectType()
    {
        $jm = new JsonMapper();
        /** @var JsonMapperTest_VariadicArray $sn */
        $sn = $jm->map(
            json_decode('{"variadicDateTime":["2014-01-02","2014-05-07"]}'),
            new JsonMapperTest_VariadicArray()
        );
        $variadicArray = $sn->getVariadicDateTime();

        $this->assertIsArray($variadicArray);
        $this->assertEquals(2, count($variadicArray));
        $this->assertInstanceOf('DateTime', $variadicArray[0]);
        $this->assertInstanceOf('DateTime', $variadicArray[1]);
        $this->assertEquals(
            '2014-01-02', $variadicArray[0]->format('Y-m-d')
        );
        $this->assertEquals(
            '2014-05-07', $variadicArray[1]->format('Y-m-d')
        );
    }
}

?>
