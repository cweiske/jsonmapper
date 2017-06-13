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
 * Automatically map JSON structures into objects.
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
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
     * Throw an exception when JSON data contain a property
     * that is not defined in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnUndefinedProperty = false;

    /**
     * Throw an exception if the JSON data miss a property
     * that is marked with @required in the PHP class
     *
     * @var boolean
     */
    public $bExceptionOnMissingData = false;

    /**
     * If the types of map() parameters shall be checked.
     *
     * You have to disable it if you're using the json_decode "assoc" parameter.
     *
     *     json_decode($str, false)
     *
     * @var boolean
     */
    public $bEnforceMapType = true;

    /**
     * Throw an exception when an object is expected but the JSON contains
     * a non-object type.
     *
     * @var boolean
     */
    public $bStrictObjectTypeChecking = false;

    /**
     * Throw an exception, if null value is found
     * but the type of attribute does not allow nulls.
     *
     * @var bool
     */
    public $bStrictNullTypes = true;

    /**
     * Allow mapping of private and proteted properties.
     *
     * @var boolean
     */
    public $bIgnoreVisibility = false;

    /**
     * Override class names that JsonMapper uses to create objects.
     * Useful when your setter methods accept abstract classes or interfaces.
     *
     * Works only when $bExceptionOnUndefinedProperty is disabled.
     *
     * Parameters to this function are:
     * 1. Object that is being filled
     * 2. Name of the unknown JSON property
     * 3. JSON value of the property
     *
     * @var array
     */
    public $classMap = array();

    /**
     * Callback used when an undefined property is found.
     *
     * @var callable
     */
    public $undefinedPropertyHandler = null;

    /**
     * Runtime cache for inspected classes. This is particularly effective if
     * mapArray() is called with a large number of objects
     *
     * @var array property inspection result cache
     */
    protected $arInspectedClasses = array();

    /**
     * Runtime cache for use clauses. Consists of an array of arrays, where the
     * index for the first level is the full class name. Each sub-array will contain
     * the "use" clauses found on the file regarding that class. This expects that
     * the class respects PSR-1, which means one single class per file, and thus one
     * single namespace. http://www.php-fig.org/psr/psr-1/#namespace-and-class-names
     *
     * @var string[][]
     */
    protected $arrUseClauses = [];

    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param object $json   JSON object structure from json_decode()
     * @param object $object Object to map $json data into
     *
     * @return object Mapped object is returned.
     * @throws JsonMapper_Exception
     * @see    mapArray()
     */
    public function map($json, $object)
    {
        if ($this->bEnforceMapType && !is_object($json)) {
            throw new InvalidArgumentException(
                'JsonMapper::map() requires first argument to be an object'
                . ', ' . gettype($json) . ' given.'
            );
        }
        if (!is_object($object)) {
            throw new InvalidArgumentException(
                'JsonMapper::map() requires second argument to be an object'
                . ', ' . gettype($object) . ' given.'
            );
        }

        $strClassName = get_class($object);
        $rc = new ReflectionClass($object);
        $strNs = $rc->getNamespaceName();
        $providedProperties = array();
        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            $providedProperties[$key] = true;

            // Store the property inspection results so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                $this->arInspectedClasses[$strClassName][$key]
                    = $this->inspectProperty($rc, $key);
            }

            list($hasProperty, $accessor, $type)
                = $this->arInspectedClasses[$strClassName][$key];

            if (!$hasProperty) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapper_Exception(
                        'JSON property "' . $key . '" does not exist'
                        . ' in object of type ' . $strClassName
                    );
                } else if ($this->undefinedPropertyHandler !== null) {
                    call_user_func(
                        $this->undefinedPropertyHandler,
                        $object, $key, $jvalue
                    );
                } else {
                    $this->log(
                        'info',
                        'Property {property} does not exist in {class}',
                        array('property' => $key, 'class' => $strClassName)
                    );
                }
                continue;
            }

            if ($accessor === null) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapper_Exception(
                        'JSON property "' . $key . '" has no public setter method'
                        . ' in object of type ' . $strClassName
                    );
                }
                $this->log(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array('property' => $key, 'class' => $strClassName)
                );
                continue;
            }

            if ($this->isNullable($type) || !$this->bStrictNullTypes) {
                if ($jvalue === null) {
                    $this->setProperty($object, $accessor, null);
                    continue;
                }
                $type = $this->removeNullable($type);
            } else if ($jvalue === null) {
                throw new JsonMapper_Exception(
                    'JSON property "' . $key . '" in class "'
                    . $strClassName . '" must not be NULL'
                );
            }

            if ($type === null || $type === 'mixed') {
                //no given type - simply set the json data
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            } else if ($this->isObjectOfSameType($type, $jvalue)) {
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            } else if ($this->isSimpleType($type)) {
                settype($jvalue, $type);
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            }

            //FIXME: check if type exists, give detailed error message if not
            if ($type === '') {
                throw new JsonMapper_Exception(
                    'Empty type at property "'
                    . $strClassName . '::$' . $key . '"'
                );
            }

            $array = null;
            $subtype = null;
            if ($this->isArrayOfType($type)) {
                //array
                $array = array();
                $subtype = substr($type, 0, -2);
            } else if (substr($type, -1) == ']') {
                list($proptype, $subtype) = explode('[', substr($type, 0, -1));
                $subtype = $this->removeNullable($subtype);

                if (!$this->isSimpleType($proptype)) {
                    $proptype = $this->getFullNamespace($proptype, $strNs);
                }
                if ($proptype == 'array') {
                    $array = array();
                } else {
                    $array = $this->createInstance($proptype);
                }
            } else {
                $type = $this->getFullNamespace($type, $strNs);
                if ($type == 'ArrayObject'
                    || is_subclass_of($type, 'ArrayObject')
                ) {
                    $array = $this->createInstance($type);
                }
            }

            if ($array !== null) {
                if (!is_array($jvalue) && $this->isFlatType(gettype($jvalue))) {
                    throw new JsonMapper_Exception(
                        'JSON property "' . $key . '" must be an array, '
                        . gettype($jvalue) . ' given'
                    );
                }

                $child = $this->mapArray($jvalue, $array, $subtype, $strNs);
            } elseif ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($this->bStrictObjectTypeChecking) {
                    throw new JsonMapper_Exception(
                        'JSON property "' . $key . '" must be an object, '
                        . gettype($jvalue) . ' given'
                    );
                }
                $type = $this->getFullNamespace($type, $strNs);
                $child = $this->createInstance($type, true, $jvalue);
            } else {
                $type = $this->getFullNamespace($type, $strNs);
                $child = $this->createInstance($type);
                $this->map($jvalue, $child);
            }
            $this->setProperty($object, $accessor, $child);
        }

        if ($this->bExceptionOnMissingData) {
            $this->checkMissingData($providedProperties, $rc);
        }

        return $object;
    }

    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string $type  Type name (simple type or class name)
     * @param string $strNs Base namespace that gets prepended to the type name
     *
     * @return string Fully-qualified type name with namespace
     */
    protected function getFullNamespace($type, $strNs)
    {
        if ($type !== '' && $type{0} != '\\') {
            //create a full qualified namespace
            if ($strNs != '') {
                $type = '\\' . $strNs . '\\' . $type;
            }
        }
        return $type;
    }

    /**
     * Check required properties exist in json
     *
     * @param array  $providedProperties array with json properties
     * @param object $rc                 Reflection class to check
     *
     * @throws JsonMapper_Exception
     *
     * @return void
     */
    protected function checkMissingData($providedProperties, ReflectionClass $rc)
    {
        foreach ($rc->getProperties() as $property) {
            $rprop = $rc->getProperty($property->name);
            $docblock = $rprop->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            if (isset($annotations['required'])
                && !isset($providedProperties[$property->name])
            ) {
                throw new JsonMapper_Exception(
                    'Required property "' . $property->name . '" of class '
                    . $rc->getName()
                    . ' is missing in JSON data'
                );
            }
        }
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
     *                      like "string" and nullability "string|null".
     *                      Pass "null" to not convert any values
     * @param string $strNs Namespace
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null, $strNs)
    {
        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            if ($class === null) {
                $array[$key] = $jvalue;
            } elseif ($this->isArrayOfType($class)) {
                $array[$key] = $this->mapArray($jvalue,
                    [],
                    substr($class, 0, -2), $strNs
                );
            } elseif ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($jvalue === null) {
                    $array[$key] = null;
                } else {
                    if ($this->isSimpleType($class)) {
                        settype($jvalue, $class);
                        $array[$key] = $jvalue;
                    } else {
                        $array[$key] = $this->createInstance(
                            $this->getFullNamespace($class, $strNs),
                            true,
                            $jvalue
                        );
                    }
                }
            } else {
                $array[$key] = $this->map(
                    $jvalue, $this->createInstance(
                        $this->getFullNamespace($class, $strNs)
                    )
                );
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
     *               Second value: the accessor to use (
     *                 ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     */
    protected function inspectProperty(ReflectionClass $rc, $name)
    {
        //try setter method first
        $setter = 'set' . $this->getCamelCaseName($name);

        if ($rc->hasMethod($setter)) {
            $rmeth = $rc->getMethod($setter);
            if ($rmeth->isPublic() || $this->bIgnoreVisibility) {
                $rparams = $rmeth->getParameters();
                if (count($rparams) > 0) {
                    $pclass = $rparams[0]->getClass();
                    if ($pclass !== null) {
                        $nullability = '';
                        if ($rparams[0]->allowsNull()) {
                            $nullability = '|null';
                        }
                        return array(
                            true, $rmeth,
                            '\\' . $pclass->getName() . $nullability
                        );
                    }
                }

                $docblock    = $rmeth->getDocComment();
                $annotations = $this->parseAnnotations($docblock);

                if (!isset($annotations['param'][0])) {
                    return array(true, $rmeth, null);
                }
                list($type) = explode(' ', trim($annotations['param'][0]));

                $type = $this->getFullyQualifiedType($rc, $type);

                return array(true, $rmeth, $type);
            }
        }

        //now try to set the property directly
        if ($rc->hasProperty($name)) {
            $rprop = $rc->getProperty($name);
        } else {
            //case-insensitive property matching
            $rprop = null;
            foreach ($rc->getProperties() as $p) {
                if ((strcasecmp($p->name, $name) === 0)) {
                    $rprop = $p;
                    break;
                }
            }
        }
        if ($rprop !== null) {
            if ($rprop->isPublic() || $this->bIgnoreVisibility) {
                $docblock    = $rprop->getDocComment();
                $annotations = $this->parseAnnotations($docblock);

                if (!isset($annotations['var'][0])) {
                    return array(true, $rprop, null);
                }

                //support "@var type description"
                list($type) = explode(' ', $annotations['var'][0]);

                $type = $this->getFullyQualifiedType($rc, $type);

                return array(true, $rprop, $type);
            } else {
                //no setter, private property
                return array(true, null, null);
            }
        }

        //no setter, no property
        return array(false, null, null);
    }


    /**
     * Splits the $type string from the phpdoc, and finds the FQN for each type
     * found
     *
     * @param ReflectionClass $rc Reflection class to check
     * @param string          $type source type
     *
     * @return string
     */
    protected function getFullyQualifiedType(ReflectionClass $rc, $type)
    {
        $arrTypes = [];
        $arrMatch = [];
        foreach (explode('|', $type) as $strType) {
            $strType = trim($strType);
            $strArraySection = '';
            if (preg_match('/(.*?)(\[.*\])$/', $strType, $arrMatch) === 1) {
                $strType = $arrMatch[1];
                $strArraySection = $arrMatch[2];
            }
            $strFQNType = $this->getFQN($rc, $strType);
            if ($strArraySection !== '') {
                if (preg_match('/^\[([\w]+)\]$/',
                        $strArraySection, $arrMatch) === 1) {
                    $strArraySection = '['.$this->getFQN($rc, $arrMatch[1]).']';
                }
            }
            $arrTypes[] = $strFQNType.$strArraySection;
        }
        return implode('|', $arrTypes);


    }

    /**
     * Gets the FQN for a type
     *
     * @param ReflectionClass $rc Reflection class to check
     * @param string          $strType source type
     *
     * @return string
     */
    protected function getFQN(ReflectionClass $rc, $strType)
    {
        // If type starts with \, no need to append namespaces on it
        if (strpos($strType, '\\') === 0) {
            return $strType;
        }
        $arrUseClauses = $this->getUseClauses($rc);

        if (count($arrUseClauses) === 0) {
            return $strType;
        }

        // If there is a perfect match for the type within the use clauses, prepend
        // a \ and return it - this will guarantee that we get the correct FQN for
        // it, even if it belongs to the root namespace
        if (array_key_exists($strType, $arrUseClauses)) {
            return '\\' . $strType;
        }

        // If the type contains a relative qualified name, we need to find the first
        // part of if within the 'use' clauses. To do that, we take the first
        // sub-path of the type and search it within the use clauses. This should
        // return exactly one match, since PHP does not allow ambiguous class names.
        if (strpos($strType, '\\') !== false) {
            // Gets first sub-path
            $arrExplodedType = explode('\\', $strType, 2);
            $strNSSubPath = $arrExplodedType[0];
            // Get use clause that end with the found sub-path
            // (There can be only one! #highlanderfeelings)
            $arrMatchingUseClauses = preg_grep(
                '/^(.*\\\\' . $strNSSubPath . '|' . $strNSSubPath . ')$/',
                $arrUseClauses
            );
            if (count($arrMatchingUseClauses) === 1) {
                return '\\' . preg_replace('/(.*)' . $strNSSubPath . '$/',
                        '$1',
                        reset($arrMatchingUseClauses)) . $strType;
            }
        } else {
            // The last check against use clauses is when type is a SFQN and its FQN
            // is in the use clauses:
            $arrMatchingUseClauses =
                preg_grep('/\\\\'.$strType.'$/', $arrUseClauses);
            if (count($arrMatchingUseClauses) === 1) {
                return '\\'.reset($arrMatchingUseClauses);
            }
        }

        // If nothing else was triggered, simply return the unchanged type
        return $strType;
    }

    /**
     * Finds all "use" clauses inside the class file, and returns a string[] with the
     * found namespace names
     *
     * @param ReflectionClass $rc Reflection class to check
     *
     * @return string[]
     */
    protected function getUseClauses(ReflectionClass $rc)
    {
        $strClassName = $rc->getName();
        if (!array_key_exists($strClassName, $this->arrUseClauses)) {

            $arrLines = file($rc->getFileName(),
                FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);

            if ($arrLines && count($arrLines)) {
                $arrUseLines = preg_grep('/^\s*use\s+[\w\\\\]+;/', $arrLines);

                // strip out the 'use ' part
                $this->arrUseClauses[$strClassName] = preg_replace(
                    '/(^\s*use\s+)([\w\\\\]+);/', '$2', $arrUseLines);

                // makes keys match values, so that $arr[x] = x;
                $this->arrUseClauses[$strClassName] = array_combine(
                    $this->arrUseClauses[$strClassName],
                    $this->arrUseClauses[$strClassName]
                );
            }
        }

        return $this->arrUseClauses[$strClassName];
    }

    /**
     * Removes - and _ and makes the next letter uppercase
     *
     * @param string $name Property name
     *
     * @return string CamelCasedVariableName
     */
    protected function getCamelCaseName($name)
    {
        return str_replace(
            ' ', '', ucwords(str_replace(array('_', '-'), ' ', $name))
        );
    }

    /**
     * Since hyphens cannot be used in variables we have to uppercase them.
     *
     * Technically you may use them, but they are awkward to access.
     *
     * @param string $name Property name
     *
     * @return string Name without hyphen
     */
    protected function getSafeName($name)
    {
        if (strpos($name, '-') !== false) {
            $name = $this->getCamelCaseName($name);
        }

        return $name;
    }

    /**
     * Set a property on a given object to a given value.
     *
     * Checks if the setter or the property are public are made before
     * calling this method.
     *
     * @param object $object   Object to set property on
     * @param object $accessor ReflectionMethod or ReflectionProperty
     * @param mixed  $value    Value of property
     *
     * @return void
     */
    protected function setProperty(
        $object, $accessor, $value
    ) {
        if (!$accessor->isPublic() && $this->bIgnoreVisibility) {
            $accessor->setAccessible(true);
        }
        if ($accessor instanceof ReflectionProperty) {
            $accessor->setValue($object, $value);
        } else {
            //setter method
            $accessor->invoke($object, $value);
        }
    }

    /**
     * Create a new object of the given type.
     *
     * This method exists to be overwritten in child classes,
     * so you can do dependency injection or so.
     *
     * @param string  $class        Class name to instantiate
     * @param boolean $useParameter Pass $parameter to the constructor or not
     * @param mixed   $parameter    Constructor parameter
     *
     * @return object Freshly created object
     */
    public function createInstance(
        $class, $useParameter = false, $parameter = null
    ) {
        if (isset($this->classMap[$class])) {
            $class = $this->classMap[$class];
        }
        if ($useParameter) {
            return new $class($parameter);
        } else {
            return new $class();
        }
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a simple PHP type
     *
     * @see isFlatType()
     */
    protected function isSimpleType($type)
    {
        return $type === 'string'
            || $type === 'boolean' || $type === 'bool'
            || $type === 'integer' || $type === 'int'
            || $type === 'double'  || $type === 'float'
            || $type === 'array'   || $type === 'object';
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param string $type  class name of type being required
     * @param mixed  $value Some PHP value to be tested
     *
     * @return boolean True if $object has type of $type
     */
    protected function isObjectOfSameType($type, $value)
    {
        if (false === is_object($value)) {
            return false;
        }

        return is_a($value, $type);
    }

    /**
     * Checks if the given type is a type that is not nested
     * (simple type except array and object)
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a non-nested PHP type
     *
     * @see isSimpleType()
     */
    protected function isFlatType($type)
    {
        return $type == 'NULL'
            || $type == 'string'
            || $type == 'boolean' || $type == 'bool'
            || $type == 'integer' || $type == 'int'
            || $type == 'double' || $type == 'float';
    }

    /**
     * Returns true if type is an array of elements
     * (bracket notation)
     *
     * @param string $strType type to be matched
     *
     * @return bool
     */
    protected function isArrayOfType($strType)
    {
        return(substr($strType, -2) === '[]');
    }

    /**
     * Checks if the given type is nullable
     *
     * @param string $type type name from the phpdoc param
     *
     * @return boolean True if it is nullable
     */
    protected function isNullable($type)
    {
        return stripos('|' . $type . '|', '|null|') !== false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param string $type type name from the phpdoc param
     *
     * @return string The new type value
     */
    protected function removeNullable($type)
    {
        if ($type === null) {
            return null;
        }
        return substr(
            str_ireplace('|null|', '|', '|' . $type . '|'),
            1, -1
        );
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
