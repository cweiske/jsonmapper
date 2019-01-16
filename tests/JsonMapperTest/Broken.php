<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

/**
 * Unit test helper class for testing property mapping
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapperTest_Broken
{
    /**
     * @var ArrayObject[ThisClassDoesNotExist]
     */
    public $pTypedArrayObjectNoClass;

    /**
     *
     * @var string
     * @required
     */
    public $pMissingData;

    /**
     * @var string
     * @enum JsonMapperTest_Broken::getEnum
     */
    public $pMethodEnum;

    /**
     * @var string
     * @enum first, second
     */
    public $pMethodList;

    public static function getEnum()
    {
        return ['first', 'second'];
    }
}
?>
