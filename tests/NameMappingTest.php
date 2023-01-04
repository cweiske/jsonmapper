<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once 'JsonMapperTest/Simple.php';

class NameMappingTest extends TestCase
{
    public function testItMapsKeyByCamelCaseName(): void
    {
        $jm = new JsonMapper();
        $jm->bMatchPropertyByCamelCase = true;

        /** @var JsonMapperTest_Simple $sn */
        $sn = $jm->map(
            json_decode('{"hyphen_value": "abc"}'),
            new JsonMapperTest_Simple()
        );

        self::assertSame('abc', $sn->hyphenValue);
    }

    public function testItDoesNotMapKeyByCamelCaseNameIfFlagIsNotSet(): void
    {
        $jm = new JsonMapper();

        /** @var JsonMapperTest_Simple $sn */
        $sn = $jm->map(
            json_decode('{"hyphen_value": "abc"}'),
            new JsonMapperTest_Simple()
        );

        self::assertNull($sn->hyphenValue);
    }
}
