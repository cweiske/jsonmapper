<?php

declare(strict_types=1);
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

use namespacetest\model\User;
use namespacetest\Unit;
use namespacetest\UnitData;

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
    public const CLASS_MAP_CLASS = 'JsonMapperTest_PlainObject';

    public const CLASS_MAP_DATA = '2016-04-14T23:15:42+02:00';

    /**
     * Abuse self
     */
    public function __invoke($class, $jvalue): string
    {
        $testCase = $this;

        // the class/interface to be mapped
        $testCase->assertEquals($testCase::CLASS_MAP_CLASS, $class);
        $testCase->assertEquals($testCase::CLASS_MAP_DATA, $jvalue);

        return 'DateTime';
    }

    public static function classMapTestData(): array
    {
        $testCase = new self('ClassMapTest');

        // classMap value
        return [
            'name' => ['DateTime'],
            'function' => [function ($class, $jvalue) use ($testCase) {
                // the class/interface to be mapped
                $testCase->assertEquals($testCase::CLASS_MAP_CLASS, $class);
                $testCase->assertEquals($testCase::CLASS_MAP_DATA, $jvalue);
                return 'DateTime';
            }],
            'invoke' => [$testCase],  // __invoke
        ];
    }

    /**
     * @dataProvider classMapTestData
     * @throws JsonMapperException
     */
    public function testClassMap($classMapValue)
    {
        $jm = new JsonMapper();
        $jm->classMap[self::CLASS_MAP_CLASS] = $classMapValue;
        $sn = $jm->map(
            json_decode('{"pPlainObject":"' . self::CLASS_MAP_DATA . '"}'),
            new JsonMapperTest_Object()
        );

        /** @var DateTimeInterface $pPlainObject */
        $pPlainObject = $sn->pPlainObject;
        $this->assertIsObject($pPlainObject);
        $this->assertInstanceOf(DateTimeInterface::class, $pPlainObject);
        $this->assertEquals(
            self::CLASS_MAP_DATA,
            $pPlainObject->format('c')
        );
    }

    /**
     * @throws JsonMapperException
     */
    public function testNamespaceKeyWithLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classMap['\\namespacetest\\model\\User']
            = Unit::class;
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new UnitData()
        );

        $this->assertInstanceOf(Unit::class, $data->user);
    }

    /**
     * @throws JsonMapperException
     */
    public function testNamespaceKeyNoLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classMap[User::class]
            = Unit::class;
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new UnitData()
        );

        $this->assertInstanceOf(Unit::class, $data->user);
    }

    /**
     * @throws JsonMapperException
     */
    public function testMapObjectToSimpleType()
    {
        $jm = new JsonMapper();
        $jm->classMap[User::class] = 'string';
        $data = $jm->map(
            json_decode('{"user":"foo"}'),
            new UnitData()
        );

        $this->assertIsString($data->user);
    }

    /**
     * @throws JsonMapperException
     */
    public function testMapArraySubtype()
    {
        $jm = new JsonMapper();
        $jm->classMap[DateTime::class] = 'string';
        $data = $jm->map(
            json_decode('{"typedSimpleArray":["2019-03-23"]}'),
            new JsonMapperTest_Array()
        );
        $this->assertIsArray($data->typedSimpleArray);
        $this->assertEquals(1, count($data->typedSimpleArray));
        $this->assertIsString($data->typedSimpleArray[0]);
    }
}
