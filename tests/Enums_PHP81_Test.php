<?php

declare(strict_types=1);

/**
 * Unit tests for JsonMapper's support for PHP 8.1 enums
 *
 * @category Tests
 * @package  JsonMapper
 * @author   Martin Reinfandt <martin.reinfandt@check24.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 8.1
 */
class Enums_PHP81_Test extends \PHPUnit\Framework\TestCase
{
    const TEST_DATA = '{"stringBackedEnum": "foo", "intBackedEnum": 2}';

    /**
     * Test for PHP8.1 enums.
     */
    public function testEnumMapping()
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = false;
        /** @var \Enums\ObjectWithEnum $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::FOO, $sn->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $sn->intBackedEnum);
    }

    /**
     * Test that string values are correctly mapped to backed enum properties.
     */
    public function testBackedEnumPropertyIsMappedFromString(): void
    {
        $json = (object) [
            'stringBackedEnum' => 'foo',
            'intBackedEnum' => 2,
        ];

        $mapper = new \JsonMapper();
        $target = new \Enums\ObjectWithEnum();

        $mapped = $mapper->map($json, $target);

        $this->assertSame(
            \Enums\StringBackedEnum::FOO,
            $mapped->stringBackedEnum,
            'Expected JSON scalar to be converted to the corresponding backed enum case'
        );
    }

    /**
     * Test that mapping invalid string values to backed enum properties throws an exception.
     */
    public function testBackedEnumPropertyWithInvalidStringThrowsJsonMapperException(): void
    {
        $json = (object) [
            'stringBackedEnum' => 'not-a-valid-enum-value',
            'intBackedEnum' => 'not-a-valid-enum-value',
        ];

        $mapper = new \JsonMapper();
        $target = new \Enums\ObjectWithEnum();

        $this->expectException(\JsonMapper_Exception::class);
        $this->expectExceptionMessage('Enum value "not-a-valid-enum-value" does not belong to');

        $mapper->map($json, $target);
    }
}
