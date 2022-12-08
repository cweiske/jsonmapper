<?php
/**
 * Unit test helper class for testing property mapping
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class JsonMapperTest_UnionTypes
{
    public DateTime|string $dateOrStringNative;

    /**
     * @var DateTime|string
     */
    public $dateOrStringDocblock;
}
