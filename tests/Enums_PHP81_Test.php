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

    public function testBackedEnumsAreAcceptedUnderStrictObjectTypeChecking(): void
    {
        $json = '{"stringBackedEnum": "foo", "intBackedEnum": 2}';

        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;

        /** @var \Enums\ObjectWithEnum $result */
        $result = $jm->map(
            json_decode($json),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnum);
    }

    public function testNullableBackedEnumAcceptsNull(): void
    {
        $json = '{"nullableStringEnum": null}';

        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;

        /** @var \Enums\ObjectWithEnumArray $result */
        $result = $jm->map(
            json_decode($json),
            new \Enums\ObjectWithEnumArray()
        );

        $this->assertNull($result->nullableStringEnum);
    }

    public function testArrayOfBackedEnums(): void
    {
        $json = '{"stringBackedEnums": ["foo", "bar"], "intBackedEnums": [1, 2]}';

        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;

        /** @var \Enums\ObjectWithEnumArray $result */
        $result = $jm->map(
            json_decode($json),
            new \Enums\ObjectWithEnumArray()
        );

        $this->assertCount(2, $result->stringBackedEnums);
        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnums[0]);
        $this->assertSame(\Enums\StringBackedEnum::BAR, $result->stringBackedEnums[1]);

        $this->assertCount(2, $result->intBackedEnums);
        $this->assertSame(\Enums\IntBackedEnum::FOO, $result->intBackedEnums[0]);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnums[1]);
    }
}
