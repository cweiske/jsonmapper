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
 * Automatically map JSON structures into objects.
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  AGPL https://www.gnu.org/licenses/agpl
 * @link     http://www.netresearch.de/
 */
class JsonMapper
{
    /**
     * PSR-3 compatible logger object
     *
     * @link http://www.php-fig.org/psr/psr-3/
     * @var  object
     * @see  setLogger()
     */
    protected $logger;



    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param object $json   JSON object structure from json_decode()
     * @param object $object Object to map $json data into
     *
     * @return object Mapped object is returned.
     * @see    mapArray()
     */
    public function map($json, $object)
    {
        $rc = new ReflectionClass($object);
        foreach ($json as $key => $jvalue) {
            list($hasProperty, $type) = $this->inspectProperty($rc, $key);
            if (!$hasProperty) {
                $this->log(
                    'info',
                    'Property {property} does not exist in {class}',
                    array('property' => $key, 'class' => get_class($object))
                );
                continue;
            }

            if ($type === null) {
                //no given type - simply set the json data
                $this->setProperty($object, $key, $jvalue);
                continue;
            } else if ($this->isSimpleType($type)) {
                settype($jvalue, $type);
                $this->setProperty($object, $key, $jvalue);
                continue;
            }

            if ($type{0} != '\\') {
                //create a full qualified namespace
                $ns = $rc->getNamespaceName();
                if ($ns != '') {
                    $type = '\\' . $ns . '\\' . $type;
                }
            }

            if($type == '\\DateTime'){
                $child = new \DateTime($jvalue);
                $this->setProperty($object, $key, $child);
                continue;
            }

            //FIXME: check if type exists, give detailled error message if not
            $array = null;
            $subtype = null;
            if (substr($type, -2) == '[]') {
                //array
                $array = array();
                $subtype = substr($type, 0, -2);
            } else if (substr($type, -1) == ']') {
                list($proptype, $subtype) = explode('[', substr($type, 0, -1));
                $array = new $proptype();
            } else if ($type == 'ArrayObject'
                || is_subclass_of($type, 'ArrayObject')
            ) {
                $array = new $type();
            }

            if ($array !== null) {
                if ($subtype{0} != '\\') {
                    //create a full qualified namespace
                    $ns = $rc->getNamespaceName();
                    if ($ns != '') {
                        $subtype = $ns . '\\' . $subtype;
                    }
                }
                $child = $this->mapArray($jvalue, $array, $subtype);
            } else {
                $child = new $type();
                $this->map($jvalue, $child);
            }
            $this->setProperty($object, $key, $child);
        }

        return $object;
    }

    /**
     * Map an array
     *
     * @param array  $json  JSON array structure from json_decode()
     * @param mixed  $array Array or ArrayObject that gets filled with
     *                      data from $json
     * @param string $class Class name for children objects.
     *                      All children will get mapped onto this type.
     *                      Supports class names and simple types
     *                      like "string".
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null)
    {
        foreach ($json as $key => $jvalue) {
            if ($class === null) {
                $array[$key] = $jvalue;
            } else {
                $array[$key] = $this->map($jvalue, new $class());
            }
        }
        return $array;
    }

    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param object $rc   Reflection class to check
     * @param string $name Property name
     *
     * @return array First value: if the property exists
     *               Second value: type of the property
     */
    protected function inspectProperty(ReflectionClass $rc, $name)
    {
        if ($rc->hasProperty($name)) {
            $rprop = $rc->getProperty($name);
            $docblock = $rprop->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            if (!isset($annotations['var'][0])) {
                return array(true, null);
            }

            //support "@var type description"
            list($type) = explode(' ', $annotations['var'][0]);

            return array(true, $type);
        }

        $setter = 'set' . ucfirst($name);
        if ($rc->hasMethod($setter)) {
            $rmeth = $rc->getMethod($setter);
            $rparams = $rmeth->getParameters();
            if (count($rparams) > 0) {
                $pclass = $rparams[0]->getClass();
                if ($pclass !== null) {
                    return array(true, $pclass->getName());
                }
            }
            $docblock = $rmeth->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            if (!isset($annotations['param'][0])) {
                return array(true, null);
            }
            list($type) = explode(' ', trim($annotations['param'][0]));
            return array(true, $type);
        }

        return array(false, null);
    }

    /**
     * Set a property on a given object to a given value
     *
     * @param object $object Object to set property on
     * @param string $name   Property name
     * @param mixed  $value  Value of property
     *
     * @return void
     */
    protected function setProperty($object, $name, $value)
    {
        $rc = new ReflectionClass($object);
        $setter = 'set' . ucfirst($name);
        if (method_exists($object, $setter)
            && $rc->getMethod($setter)->isPublic()
        ) {
            $object->$setter($value);
        } else if ($rc->getProperty($name)->isPublic()) {
            $object->$name = $value;
        } else {
            $this->log(
                'error',
                'Property {class}::{property} cannot be set from outside',
                array('property' => $name, 'class' => get_class($object))
            );
        }
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a simple PHP type
     */
    protected function isSimpleType($type)
    {
        return $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'
            || $type == 'float' || $type == 'array' || $type == 'object';
    }

    /**
     * Copied from PHPUnit 3.7.29, Util/Test.php
     *
     * @param string $docblock Full method docblock
     *
     * @return array
     */
    protected static function parseAnnotations($docblock)
    {
        $annotations = array();
        // Strip away the docblock header and footer
        // to ease parsing of one line annotations
        $docblock = substr($docblock, 3, -2);

        $re = '/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m';
        if (preg_match_all($re, $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        return $annotations;
    }

    /**
     * Log a message to the $logger object
     *
     * @param string $level   Logging level
     * @param string $message Text to log
     * @param array  $context Additional information
     *
     * @return null
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger PSR-3 compatible logger object
     *
     * @return null
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
?>
