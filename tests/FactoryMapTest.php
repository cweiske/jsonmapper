<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
require_once 'JsonMapperTest/Object.php';

/**
 * Unit tests for JsonMapper's factoryMap
 *
 * @category Tools
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class FactoryMapTest extends \PHPUnit\Framework\TestCase
{
    public function testFactory()
    {
        $jm = new JsonMapper();
        $jm->factoryMap["DateTime"] = function($jvalue) {
            $date = new DateTime();
            $date->setTimestamp($jvalue);
            return $date;
        };
        $sn = $jm->map(
                json_decode('{"datetime":1569583404}'),
                new JsonMapperTest_Object()
                );
        $this->assertInstanceOf('DateTime', $sn->datetime);
        $this->assertEquals(
                '2019-09-27T11:23:24+00:00',
                $sn->datetime->format('c')
        );
    }
}
?>
