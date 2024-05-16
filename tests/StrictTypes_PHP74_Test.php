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
class StrictTypes_PHP74_Test extends \PHPUnit\Framework\TestCase
{
    const TEST_DATA = '{"id": 123, "importedNs": {"name": "Name"}, "otherNs": {"name": "Foo"}, "withoutType": "anything", "docDefinedType": {"name": "Name"}, "nullable": "value", "fooArray": [{"name": "Foo 1"}, {"name": "Foo 2"}]}';

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

        $this->assertSame(123, $sn->id);
        $this->assertInstanceOf(\namespacetest\model\User::class, $sn->importedNs);
        $this->assertInstanceOf(\othernamespace\Foo::class, $sn->otherNs);
        $this->assertSame('anything', $sn->withoutType);
        $this->assertSame('value', $sn->nullable);
        $this->assertIsArray($sn->fooArray);
        $this->assertCount(2, $sn->fooArray);
        $this->assertInstanceOf(\othernamespace\Foo::class, $sn->fooArray[0]);
        $this->assertInstanceOf(\othernamespace\Foo::class, $sn->fooArray[1]);
    }
}
