<?php

use namespacetest\ObjectWithEnum;

/**
 * Unit tests for JsonMapper's support for PHP 8.1 enums
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Aleksey Pilov <aleksey.pilov@ya.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 * @requires PHP 8.1
 */
class Enums_PHP81_Test extends \PHPUnit\Framework\TestCase
{
    const TEST_DATA = '{"testData": "foo"}';

    protected function setUp(): void
    {
        require_once 'Enums/TestEnum.php';
        require_once 'Enums/ObjectWithEnum.php';
    }

    /**
     * Test for PHP8.1 enums.
     */
    public function testEnumMapping()
    {
        $jm = new JsonMapper();
        /** @var \namespacetest\ObjectWithEnum $sn */
        $sn = $jm->map(
            json_decode(self::TEST_DATA),
            new ObjectWithEnum()
        );

        $this->assertEquals(TestEnum::FOO, $sn->testData);
    }
}
