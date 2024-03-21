<?php

declare(strict_types=1);

/**
 * Unit tests for JsonMapper's support for PHP 8.1 enums
 *
 * @category Tools
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
        /** @var \Enums\ObjectWithEnum $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA),
            new \Enums\ObjectWithEnum()
        );

        $this->assertSame(\Enums\StringBackedEnum::FOO, $sn->stringBackedEnum);
        $this->assertSame(\Enums\IntBackedEnum::BAR, $sn->intBackedEnum);
    }
}
