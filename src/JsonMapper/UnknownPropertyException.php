<?php
/**
 *
 * @package  JsonMapper
 * @author   Laurent Jouanneau <dev@ljouanneau.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

/**
 * general exception for bad types
 *
 * @package  JsonMapper
 * @author   Laurent Jouanneau <dev@ljouanneau.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapper_UnknownPropertyException extends JsonMapper_Exception
{
    protected $jsonPath = "";

    public function __construct ($message = "", $jsonPath = '', Throwable $previous = NULL, $code = 0) {
        parent::__construct($message, $code, $previous);
        $this->jsonPath = $jsonPath;
    }

    public function getJsonPath() {
        return $this->jsonPath;
    }
}

