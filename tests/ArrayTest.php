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
        $this->assertCount(2, $sn->typedArray);
        $this->assertContainsOnlyInstancesOf(JsonMapperTest_Simple::class, $sn->typedArray);
        $this->assertSame('stringvalue', $sn->typedArray[0]->str);
        $this->assertSame(1.2, $sn->typedArray[1]->fl);
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
        $this->assertIsArray($sn->typedSimpleArray);
        $this->assertCount(3, $sn->typedSimpleArray);
        $this->assertInstanceOf(DateTime::class, $sn->typedSimpleArray[0]);
        $this->assertNull($sn->typedSimpleArray[1]);
        $this->assertInstanceOf(DateTime::class, $sn->typedSimpleArray[2]);
        $this->assertSame(
            '2014-01-02', $sn->typedSimpleArray[0]->format('Y-m-d')
        );
        $this->assertSame(
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
        $this->assertInstanceOf(JsonMapperTest_Simple::class, $sn);
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
        $this->assertSame([1.23, 3.14, 2.048], $sn->flArray);
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
        $this->assertSame(['foo' => 1.23, 'bar' => 3.14, 'baz' => 2.048], $sn->flArray);
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
        $this->assertSame(['str', '', '2.048'], $sn->strArray);
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
        $this->assertSame(['str', '', '2.048'], $sn->strArrayV2);
    }

    public function testNullArrayValue()
    {
        $jm = new JsonMapper();
        $jm->bStrictNullTypes = true;
        $sn = $jm->map(
            json_decode('{"strArray":["a",null,"c"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertSame(['a', null, 'c'], $sn->strArray);
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
        $this->assertInstanceOf(ArrayObject::class, $sn->pArrayObject);
        $this->assertCount(2, $sn->pArrayObject);
        $this->assertContainsOnlyInstancesOf(stdClass::class, $sn->pArrayObject);
        $this->assertSame('stringvalue', $sn->pArrayObject[0]->str);
        $this->assertSame('1.2', $sn->pArrayObject[1]->fl);
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
        $this->assertInstanceOf(ArrayObject::class, $sn->pTypedArrayObject);
        $this->assertCount(2, $sn->pTypedArrayObject);
        $this->assertContainsOnlyInstancesOf(JsonMapperTest_Simple::class, $sn->pTypedArrayObject);
        $this->assertSame('stringvalue', $sn->pTypedArrayObject[0]->str);
        $this->assertSame(1.2, $sn->pTypedArrayObject[1]->fl);
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
        $this->assertInstanceOf(ArrayObject::class, $sn->pSimpleArrayObject);
        $this->assertCount(2, $sn->pSimpleArrayObject);
        $this->assertContainsOnly('int', $sn->pSimpleArrayObject, true);
        $this->assertSame(1, $sn->pSimpleArrayObject['eins']);
        $this->assertSame(1, $sn->pSimpleArrayObject['zwei']);
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
        $this->assertInstanceOf(ArrayAccess::class, $sn->pArrayAccessCollection);
        $this->assertSame(1, $sn->pArrayAccessCollection['eins']);
        $this->assertSame('two', $sn->pArrayAccessCollection['zwei']);
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
        $this->assertInstanceOf(ArrayObject::class, $sn->pTypedArrayObjectNoClass);
        $this->assertCount(1, $sn->pTypedArrayObjectNoClass);
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
        $this->assertSame(['x' => 'X'], $ao->getArrayCopy());
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
        $this->assertSame(['x' => 'X'], $ao->getArrayCopy());
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
        $this->assertSame([[1,2],[3,4],[5]], $sn->nMatrix);
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
        $this->assertCount(1, $sn->pMultiverse);

        $this->assertIsArray($sn->pMultiverse[0]);
        $this->assertCount(1, $sn->pMultiverse[0]);

        $this->assertIsArray($sn->pMultiverse[0][0]);
        $this->assertCount(1, $sn->pMultiverse[0][0]);

        $this->assertInstanceOf(
            JsonMapperTest_Simple::class, $sn->pMultiverse[0][0][0]
        );
        $this->assertSame(23, $sn->pMultiverse[0][0][0]->pint);
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
        $this->assertSame([1, 2, 3], $mapped);
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
        $this->assertSame(['en-US' => 'foo', 'de-DE' => 'bar'], $mapped);
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
        $this->assertCount(1, $sn->typedSimpleArray);
        $this->assertArrayHasKey('en-US', $sn->typedSimpleArray);
        $this->assertInstanceOf(DateTime::class, $sn->typedSimpleArray['en-US']);
        $this->assertSame(
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
        $this->assertCount(2, $zoo->animals);

        $this->assertInstanceOf(Cat::class, $zoo->animals[0]);
        $this->assertSame('Lion', $zoo->animals[0]->name);

        $this->assertInstanceOf(Fish::class, $zoo->animals[1]);
        $this->assertSame('Clown Fish', $zoo->animals[1]->name);
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

        $this->assertSame([1,2,3], $variadicArray);
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

        $this->assertCount(2, $variadicArray);
        $this->assertContainsOnlyInstancesOf(DateTime::class, $variadicArray);
        $this->assertSame('2014-01-02', $variadicArray[0]->format('Y-m-d'));
        $this->assertSame('2014-05-07', $variadicArray[1]->format('Y-m-d'));
    }

    /**
     * Test the "if (count($parameters) !== 1) {" condition in "hasVariadicArrayType()"
     */
    public function testMapArrayVariadicMethodWithMultipleParams()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"multipleParams":[23]}'),
            new JsonMapperTest_VariadicArray()
        );

        $this->assertSame([23], $sn->multipleParamsVal);
    }
}

?>
