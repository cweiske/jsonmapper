<?php

/**
 * Unit tests for JsonMapper's support for PHP 7.4 strict types
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 7.4
 */
class PHP74_StrictTypesTest extends \PHPUnit\Framework\TestCase
{
    const TEST_DATA = '{"id": 123, "importedNs": {"name": "Name"}, "otherNs": {"name": "Foo"}, "withoutType": "anything", "docDefinedType": {"name": "Name"}, "nullable": "value"}';
    const TEST_WITH_ARRAY_DATA = '{"id": 123, "users":  [{"name": "Name"},{"name": "Name2"}],  "simpleArray":  [{"name": "Name"},{"name": "Name2"}]}';

    /**
     * Sets up test cases loading required classes.
     *
     * This is in setUp and not at the top of this file to ensure this is only
     * executed with PHP 7.4 (due to the `@requires` tag).
     */
    protected function setUp()
    {
        require_once 'namespacetest/PhpStrictTypes.php';
        require_once 'namespacetest/PhpWithArrayStrictTypes.php';
        require_once 'namespacetest/model/User.php';
        require_once 'othernamespace/Foo.php';
    }

    /**
     * Test for PHP7.4 strict types.
     */
    public function testStrictTypesMapping()
    {
        $jm = new JsonMapper();
        /** @var \namespacetest\PhpStrictTypes $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA),
            new \namespacetest\PhpStrictTypes()
        );

        $this->assertEquals(123, $sn->id);
        $this->assertInstanceOf(\namespacetest\model\User::class, $sn->importedNs);
        $this->assertInstanceOf(\othernamespace\Foo::class, $sn->otherNs);
        $this->assertEquals('anything', $sn->withoutType);
        $this->assertTrue(isset($sn->nullable));
    }

    public function testArrayOfObjectsTypeMapping()
    {

        $jm = new JsonMapper();
        /** @var \namespacetest\PhpStrictTypes $sn */
        $sn = $jm->map(
            json_decode(self::TEST_WITH_ARRAY_DATA),
            new \namespacetest\PhpWithArrayStrictTypes()
        );

       $this->assertInstanceOf(\namespacetest\model\User::class, $sn->users[0]);
       $this->assertInstanceOf(stdClass::class, $sn->simpleArray[0]);
    }
}
