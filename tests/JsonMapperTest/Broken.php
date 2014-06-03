<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL https://www.gnu.org/licenses/agpl
 * @link     http://www.netresearch.de/
 */

/**
 * Unit test helper class for testing property mapping
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL https://www.gnu.org/licenses/agpl
 * @link     http://www.netresearch.de/
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
}
?>
