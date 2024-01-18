<?php

/**
 * Unit tests for JsonMapper's support for PHP 8.0 mixed type
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 8.0
 */
class MixedType_PHP80_Test extends \PHPUnit\Framework\TestCase
{
    const TEST_DATA_COMPLEX = '{"data": { "id": 123, "name": "Test User" }}';
    const TEST_DATA_SIMPLE = '{"data": 123}';

    /**
     * Sets up test cases loading required classes.
     *
     * This is in setUp and not at the top of this file to ensure this is only
     * executed with PHP 8.0 (due to the `@requires` tag).
     */
    protected function setUp(): void
    {
        require_once 'namespacetest/PhpMixedType.php';
    }

    /**
     * Test for PHP 8.0 mixed type containing an object.
     */
    public function testStrictTypesMapping_ComplexValue()
    {
        $jm = new JsonMapper();
        /** @var \namespacetest\PhpMixedType $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA_COMPLEX),
            new \namespacetest\PhpMixedType()
        );

        $this->assertInstanceOf(stdClass::class, $sn->data);
        $this->assertEquals(123, $sn->data->id);
        $this->assertEquals('Test User', $sn->data->name);
    }

    /**
     * Test for PHP 8.0 mixed type containing an int.
     */
    public function testStrictTypesMapping_SimpleValue()
    {
        $jm = new JsonMapper();
        /** @var \namespacetest\PhpMixedType $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA_SIMPLE),
            new \namespacetest\PhpMixedType()
        );

        $this->assertEquals(123, $sn->data);
    }
}
