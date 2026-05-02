<?php

declare(strict_types=1);

/**
 * Unit tests for JsonMapper's BackedEnum support when
 * bStrictObjectTypeChecking is enabled.
 *
 * Regression coverage for https://github.com/cweiske/jsonmapper/issues/247
 * "Strict object type checking doesn't allow backed enums."
 *
 * Backed enums in JSON are represented as their scalar backing value
 * (string or int). The library's strict-object check normally rejects
 * scalar-to-object conversion, but BackedEnum is a special, well-defined
 * PHP construct where the scalar IS the canonical wire representation,
 * so it must be exempted from that check.
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 8.1
 */
class EnumsStrictTypes_PHP81_Test extends \PHPUnit\Framework\TestCase
{
    /**
     * Build a JsonMapper with strict object type checking explicitly
     * left at its default value (true), so the tests exercise the
     * regression path from issue #247.
     */
    private function strictMapper(): JsonMapper
    {
        $jm = new JsonMapper();
        // Make the assumption explicit even though true is the default,
        // so the test still passes if the default ever changes.
        $jm->bStrictObjectTypeChecking = true;
        return $jm;
    }

    /**
     * The original bug: mapping a string-backed and an int-backed enum
     * on the same object with strict checking enabled used to throw
     * "must be an object, string given" / "must be an object, integer given".
     */
    public function testBackedEnumsAreAcceptedUnderStrictObjectTypeChecking(): void
    {
        $json = '{"stringBackedEnum": "foo", "intBackedEnum": 2}';

        /** @var \Enums\ObjectWithEnum $result */
        $result = $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnum);
    }

    /**
     * String-backed enum on its own under strict mode.
     */
    public function testStringBackedEnumUnderStrictMode(): void
    {
        $json = '{"stringBackedEnum": "bar", "intBackedEnum": 1}';

        /** @var \Enums\ObjectWithEnum $result */
        $result = $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::BAR, $result->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::FOO, $result->intBackedEnum);
    }

    /**
     * An invalid enum value must still be rejected — the fix should not
     * silently swallow bad input. PHP's BackedEnum::from() throws
     * \ValueError, which JsonMapper propagates.
     */
    public function testInvalidBackedEnumValueStillThrows(): void
    {
        $json = '{"stringBackedEnum": "not-a-case", "intBackedEnum": 1}';

        $this->expectException(\ValueError::class);

        $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnum()
        );
    }

    /**
     * Passing a value of the wrong scalar shape for a BackedEnum (e.g.
     * the int 42 for a string-backed enum) must still be rejected.
     * In PHP 8.5 the int is coerced to the string "42", which is not
     * a valid backing value for either case, so BackedEnum::from
     * raises \ValueError. Older PHPs may raise \TypeError instead;
     * we accept either via a common ancestor (\Throwable) and assert
     * the message mentions the offending value.
     */
    public function testWrongScalarTypeForBackedEnumStillThrows(): void
    {
        $json = '{"stringBackedEnum": 42, "intBackedEnum": 1}';

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessageMatches('/42|backing value/');

        $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnum()
        );
    }

    /**
     * Strict mode must still reject non-enum object properties when
     * given a scalar. The fix is intentionally narrow and must not
     * relax checking for everything.
     */
    public function testStrictCheckingStillRejectsNonEnumObjectFromScalar(): void
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pValueObject" must be an object, string given');

        $this->strictMapper()->map(
            json_decode('{"pValueObject":"abc"}'),
            new JsonMapperTest_Object()
        );
    }

    /**
     * A nullable BackedEnum property accepts JSON null without going
     * through the strict-object code path at all. Verifies the fix
     * does not regress null handling.
     */
    public function testNullableBackedEnumAcceptsNull(): void
    {
        $json = '{"stringBackedEnums": [], "intBackedEnums": [], "nullableStringEnum": null}';

        /** @var \Enums\ObjectWithEnumArray $result */
        $result = $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnumArray()
        );

        $this->assertNull($result->nullableStringEnum);
    }

    /**
     * Array of BackedEnum: each scalar element of the JSON array must
     * be converted to the corresponding enum case under strict mode.
     * This exercises the second, separate fix site in mapArray().
     */
    public function testArrayOfStringBackedEnumsUnderStrictMode(): void
    {
        $json = '{"stringBackedEnums": ["foo", "bar", "foo"], "intBackedEnums": []}';

        /** @var \Enums\ObjectWithEnumArray $result */
        $result = $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnumArray()
        );

        $this->assertCount(3, $result->stringBackedEnums);
        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnums[0]);
        $this->assertSame(\Enums\StringBackedEnum::BAR, $result->stringBackedEnums[1]);
        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnums[2]);
    }

    /**
     * Array of int-backed enums under strict mode.
     */
    public function testArrayOfIntBackedEnumsUnderStrictMode(): void
    {
        $json = '{"stringBackedEnums": [], "intBackedEnums": [1, 2, 1, 2]}';

        /** @var \Enums\ObjectWithEnumArray $result */
        $result = $this->strictMapper()->map(
            json_decode($json),
            new \Enums\ObjectWithEnumArray()
        );

        $this->assertCount(4, $result->intBackedEnums);
        $this->assertSame(\Enums\IntBackedEnum::FOO, $result->intBackedEnums[0]);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnums[1]);
        $this->assertSame(\Enums\IntBackedEnum::FOO, $result->intBackedEnums[2]);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnums[3]);
    }

    /**
     * Sanity check that the existing non-strict path also still works.
     * Mirrors Enums_PHP81_Test::testEnumMapping but kept here so this
     * test file can be read as a complete BackedEnum spec.
     */
    public function testBackedEnumsStillWorkWithStrictModeDisabled(): void
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = false;

        /** @var \Enums\ObjectWithEnum $result */
        $result = $jm->map(
            json_decode('{"stringBackedEnum": "foo", "intBackedEnum": 2}'),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::FOO, $result->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $result->intBackedEnum);
    }
}
