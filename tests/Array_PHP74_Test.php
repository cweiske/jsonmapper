<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for JsonMapper's support for PHP 7.4 typed arrays
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Martin Reinfandt <martin.reinfandt@check24.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 7.4
 */
class Array_PHP74_Test extends TestCase
{
    protected function setUp(): void
    {
        require_once 'JsonMapperTest/PHP74_Array.php';
    }

    public function testJsonMapper()
    {
        $json = json_decode('{"files": ["test.txt"]}');
        $jsonMapper = new \JsonMapper();
        $array = $jsonMapper->map($json, new PHP74_Array());
        self::assertCount(1, $array->files);
    }
}
