<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
require_once __DIR__ . '/../JsonMapperTest/Simple.php';

/**
 * Unit tests for JsonMapper option "bRemoveUndefinedAttributes".
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class Options_ConvertSnakeCaseTest extends \PHPUnit\Framework\TestCase
{
    public function testConvertSnakeCase()
    {
        $jm = new JsonMapper();
        $jm->bConvertSnakeCase = true;
        $obj = $jm->map(
            json_decode('{"hyphen_value":"abc"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertIsString($obj->hyphenValue);
        $this->assertFalse(isset($obj->pboolean));
        $this->assertFalse(isset($obj->pint));
    }
}
?>
