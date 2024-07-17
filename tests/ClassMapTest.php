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
 * Unit tests for JsonMapper's classMap
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ClassMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Abuse self
     */
    public function __invoke($class, $jvalue)
    {
        $testCase = $this;

        // the class/interface to be mapped
        $testCase->assertSame($testCase::CLASS_MAP_CLASS, $class);
        $testCase->assertSame($testCase::CLASS_MAP_DATA, $jvalue);

        return 'DateTime';
    }

    const CLASS_MAP_CLASS = 'JsonMapperTest_PlainObject';
    const CLASS_MAP_DATA = '2016-04-14T23:15:42+02:00';

    public static function classMapTestData()
    {
        // classMap value
        return [
            'name' =>     ['DateTime'],
            'function' => [function ($class, $jvalue) {
                // the class/interface to be mapped
                static::assertSame(static::CLASS_MAP_CLASS, $class);
                static::assertSame(static::CLASS_MAP_DATA, $jvalue);
                return 'DateTime';
            }]
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

        $this->assertInstanceOf(DateTime::class, $sn->pPlainObject);
        $this->assertSame(
            self::CLASS_MAP_DATA,
            $sn->pPlainObject->format('c')
        );
    }

    public function testNamespaceKeyWithLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classMap['\\namespacetest\\model\\User']
            = \namespacetest\Unit::class;
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new \namespacetest\UnitData()
        );

        $this->assertInstanceOf(\namespacetest\Unit::class, $data->user);
    }

    public function testNamespaceKeyNoLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classMap[\namespacetest\model\User::class]
            = \namespacetest\Unit::class;
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new \namespacetest\UnitData()
        );

        $this->assertInstanceOf(\namespacetest\Unit::class, $data->user);
    }

    public function testMapObjectToSimpleType()
    {
        $jm = new JsonMapper();
        $jm->classMap[\namespacetest\model\User::class] = 'string';
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new \namespacetest\UnitData()
        );

        $this->assertIsString($data->user);
    }

    public function testMapArraySubtype()
    {
        $jm = new JsonMapper();
        $jm->classMap[DateTime::class] = 'string';
        $data = $jm->map(
            json_decode('{"typedSimpleArray":["2019-03-23"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($data->typedSimpleArray);
        $this->assertCount(1, $data->typedSimpleArray);
        $this->assertSame('2019-03-23', $data->typedSimpleArray[0]);
    }
}
?>
