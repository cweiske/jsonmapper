<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */

namespace apimatic\jsonmapper;

/**
 * Simple exception
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class JsonMapperException extends \Exception
{
    /**
     * Exception for discarded comments setting in configuration.
     * 
     * @param array $concernedKeys Keys (PHP directives) with issues
     * 
     * @return JsonMapperException
     */
    static function commentsDisabledInConfigurationException($concernedKeys)
    {
        $concernedKeys = implode(", ", $concernedKeys);

        return new self(
            "Comments cannot be discarded in the configuration file i.e." .
            " the php.ini file; doc comments are a requirement for JsonMapper." .
            " Following configuration keys must have a value set to \"1\":" .
            " {$concernedKeys}."
        );
    }
}
?>
