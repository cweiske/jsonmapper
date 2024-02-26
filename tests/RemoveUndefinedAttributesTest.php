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

/**
 * Unit tests for JsonMapper option "bRemoveUndefinedAttributes".
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class RemoveUndefinedAttributesTest extends \PHPUnit\Framework\TestCase
{
    public function testRemoveUndefinedAttributes()
    {
        $jm = new JsonMapper();
        $jm->bRemoveUndefinedAttributes = true;
        $obj = $jm->map(
            json_decode('{"pbool":true}'),
            new JsonMapperTest_Simple()
        );
        $this->assertTrue($obj->pbool);
        $this->assertFalse(isset($obj->pboolean));
        $this->assertFalse(isset($obj->pint));
    }
}
?>
