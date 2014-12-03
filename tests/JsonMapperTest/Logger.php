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
 * Simple in-memory logger
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL https://www.gnu.org/licenses/agpl
 * @link     http://www.netresearch.de/
 */
class JsonMapperTest_Logger
{
    public $log = array();

    /**
     * Log a message to the $logger object
     *
     * @param string $level   Logging level
     * @param string $message Text to log
     * @param array  $context Additional information
     *
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->log[] = array($level, $message, $context);
    }
}
?>