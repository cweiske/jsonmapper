<?php

declare(strict_types=1);
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

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for JsonMapper's object handling (events)
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class EventTest extends TestCase
{
    /**
     * Test for deserialize post event
     *
     * @throws \JsonMapperException
     */
    public function testDeserializePostEvent()
    {
        $jm = new JsonMapper();
        $jm->postMappingMethod = '_deserializePostEvent';
        /** @var JsonMapperTest_EventObject $sn */
        $sn = $jm->map(
            json_decode('{"pStr":"one"}', false),
            new JsonMapperTest_EventObject()
        );
        $this->assertIsString($sn->pStr);
        $this->assertEquals('two', $sn->pStr);
    }

    public function testDeserializePostEventArguments()
    {
        $jm = new JsonMapper();
        $jm->postMappingMethod = '_deserializePostEventWithArguments';
        $jm->postMappingMethodArguments = [3, 'bar'];
        /** @var JsonMapperTest_EventObject $sn */
        $sn = $jm->map(
            json_decode('{"pStr":"one"}', false),
            new JsonMapperTest_EventObject()
        );
        $this->assertIsString($sn->pStr);
        $this->assertEquals('barbarbar', $sn->pStr);
    }
}
