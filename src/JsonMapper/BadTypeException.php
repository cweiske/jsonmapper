<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Laurent Jouanneau <dev@ljouanneau.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

/**
 * General exception for bad types
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Laurent Jouanneau <dev@ljouanneau.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapper_BadTypeException extends JsonMapper_Exception
{
    /**
     * @var string the path in JSON to the item that causes the error
     */
    protected $jsonPath = "";

    /**
     * JsonMapper_BadTypeException constructor.
     *
     * @param string         $message  the error message
     * @param string         $jsonPath the path in JSON to the item that causes
     *                                 the error
     * @param int            $code     the error code
     * @param Throwable|null $previous the parent exception
     */
    public function __construct ($message = "", $jsonPath = '', $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->jsonPath = $jsonPath;
    }

    /**
     * gets the json path of the error
     *
     * @return string
     */
    public function getJsonPath()
    {
        return $this->jsonPath;
    }
}
