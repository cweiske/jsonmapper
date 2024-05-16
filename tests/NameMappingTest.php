<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class NameMappingTest extends TestCase
{
    public function testItSetKeysIfReturnedByUndefinedPropertyHandler(): void
    {
        $jm = new JsonMapper();
        $jm->undefinedPropertyHandler = function (
            JsonMapperTest_Simple $object,
            string $key,
            $value
        ): string {
            $this->assertSame('hyphen_value', $key);
            return 'hyphenValue';
        };

        /** @var JsonMapperTest_Simple $sn */
        $sn = $jm->map(
            json_decode('{"hyphen_value": "abc"}'),
            new JsonMapperTest_Simple()
        );

        $this->assertSame('abc', $sn->hyphenValue);
    }

    public function testItDoesNotMapKeyIfUndefinedPropertyHandlerDoesNotReturnValue(): void
    {
        $jm = new JsonMapper();
        $jm->undefinedPropertyHandler = function (
            JsonMapperTest_Simple $object,
            string $key,
            $value
        ): void {};

        /** @var JsonMapperTest_Simple $sn */
        $sn = $jm->map(
            json_decode('{"hyphen_value": "abc"}'),
            new JsonMapperTest_Simple()
        );

        $this->assertNull($sn->hyphenValue);
    }
}
