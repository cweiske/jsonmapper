<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Laurent Jouanneau <dev@ljouanneau.com>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use phpDocumentor\Reflection\Types\Context;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Compound;

use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Void_;
use phpDocumentor\Reflection\Types\Resource_;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\Collection;

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
     * Throw an exception when an object is expected but the JSON contains
     * a non-object type.
     *
     * @var boolean
     */
    public $bStrictObjectTypeChecking = false;

    /**
     * Throw an exception when a JSON value has a simple type that implies
     * a lossy data conversion when converting to the expected type.
     *
     * @var boolean
     */
    public $bStrictSimpleTypeConversionChecking = false;

    /**
     * if lossy data conversion for simple type should be checked
     *
     * @var boolean
     */
    public $bSimpleTypeLossyDataConversionChecking = false;


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
     * @var array
     */
    public $classMap = array();

    /**
     * Callback used when an undefined property is found.
     *
     * Works only when $bExceptionOnUndefinedProperty is disabled.
     *
     * Parameters to this function are:
     * 1. Object that is being filled
     * 2. Name of the unknown JSON property
     * 3. JSON value of the property
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
        if (!is_object($json)) {
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
        return $this->mapObject($json, $object, '{}');
    }

    /**
     * maps the given JSON value to an object
     *
     * @param object $json           JSON object structure from json_decode()
     * @param object $object         Object to map $json data into
     * @param string $jsonPath       the path to the json object in the json value
     * @param bool   $strictChecking true if the object should not have a bad
     *                               property
     *
     * @return mixed
     * @throws JsonMapper_BadPropertyTypeException
     * @throws JsonMapper_BadTypeException
     * @throws JsonMapper_UnknownPropertyException
     */
    protected function mapObject($json, $object, $jsonPath = '',
        $strictChecking = false
    ) {
        if (!is_object($json)) {
            throw new JsonMapper_BadTypeException("Is not an object", $jsonPath);
        }

        $strClassName = get_class($object);
        $rc = new ReflectionClass($object);
        $classNamespace = $rc->getNamespaceName();
        $resolverContext = new Context($classNamespace, []);
        $providedProperties = array();

        // first, check that the object has all properties. If it is not
        // the case, so we don't check against the good class, and
        // we should stop to check against the next type when we are in
        // the context of list of type
        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            $providedProperties[$key] = true;

            // Store the property inspection results so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                try {
                    $this->arInspectedClasses[$strClassName][$key]
                        = $this->inspectProperty($rc, $key, $resolverContext);
                } catch (\Exception $e) {
                    throw new JsonMapper_Exception(
                        "Error during the parsing of the type for $jsonPath: "
                        . $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }
            }

            list($hasProperty, $accessor, $type)
                = $this->arInspectedClasses[$strClassName][$key];

            if (!$hasProperty) {
                if ($this->bExceptionOnUndefinedProperty || $strictChecking) {
                    throw new JsonMapper_UnknownPropertyException(
                        'JSON property "' . $jsonPath . '->' . $key
                        . '" does not exist in object of type ' . $strClassName,
                        $jsonPath . '->' . $key
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
                        array(
                            'property' => $jsonPath . '->' . $key,
                            'class' => $strClassName
                        )
                    );
                }
                continue;
            }
            if ($accessor === null) {
                if ($this->bExceptionOnUndefinedProperty || $strictChecking) {
                    throw new JsonMapper_UnknownPropertyException(
                        'JSON property "' . $jsonPath . '->' . $key
                        . '" has no public setter method in object of type '
                        . $strClassName,
                        $jsonPath . '->' . $key
                    );
                }
                $this->log(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array(
                        'property' => $jsonPath . '->' . $key,
                        'class' => $strClassName
                    )
                );
                continue;
            }
        }

        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            list($hasProperty, $accessor, $type)
                = $this->arInspectedClasses[$strClassName][$key];
            if (!$hasProperty || !$accessor) {
                continue;
            }
            $propPath = $jsonPath . '->' . $key;
            try {
                $value = $this->getPHPValue(
                    $jvalue, $propPath, $type, $strictChecking
                );
            }
            catch(JsonMapper_BadTypeException $exception) {
                throw new JsonMapper_BadPropertyTypeException(
                    'JSON property "' . $propPath . '" has not the expected type '
                    . $type . ' in object of type ' . $strClassName. ': '
                    . $exception->getMessage(),
                    $propPath,
                    $exception->getCode(),
                    $exception
                );
            }
            catch(JsonMapper_Exception $exception) {
                throw new JsonMapper_Exception(
                    'Error for JSON property "' . $jsonPath . '->' . $key .
                    '" for class '. $strClassName. ': '.$exception->getMessage(),
                    $exception->getCode(),
                    $exception
                );
            }
            $this->setProperty($object, $accessor, $value);
        }

        if ($this->bExceptionOnMissingData) {
            $this->checkMissingData($providedProperties, $rc, $jsonPath);
        }

        return $object;
    }

    /**
     * Gets the PHP value corresponding to the given JSON value.
     *
     * The value may be converted to specified object, an array object etc..
     *
     * @param mixed  $jvalue         The value to convert
     * @param string $jsonPath       The path to the value into the global JSON
     *                               structure
     * @param Type   $type           The expected type of the JSON value
     * @param bool   $strictChecking true if type value should be strictly equals
     *                               to the expected type
     *
     * @return mixed the converted value
     * @throws JsonMapper_Exception
     */
    protected function getPHPValue($jvalue, $jsonPath, Type $type = null,
        $strictChecking = false
    ) {
        if ($type === null) {
            /*throw new JsonMapper_Exception(
                'Empty type at property "'
                . $strClassName . '::$' . $key . '"'
            );*/
            return $jvalue;
        }

        if ($type instanceof Void_) {
            if ($jvalue == null) {
                return null;
            }
            throw new JsonMapper_BadTypeException(
                'JSON value at '.$jsonPath.' must be NULL (void)',
                $jsonPath
            );
        }

        if ($type instanceof Resource_ || $type instanceof Callable_) {
            throw new JsonMapper_BadTypeException(
                'JSON value at '.$jsonPath.' should not be given, as target '
                .'property must contain a resource or a callable',
                $jsonPath
            );
        }

        if ($this->isNullable($type) || !$this->bStrictNullTypes) {
            if ($jvalue === null) {
                return null;
            }
            $type = $this->removeNullable($type);
        } else if ($jvalue === null) {
            throw new JsonMapper_BadTypeException(
                'JSON value at '.$jsonPath.' must not be NULL',
                $jsonPath
            );
        }

        if ($type instanceOf Null_ || $type instanceof Mixed_) {
            //no given type - simply set the json data
            return $jvalue;
        }

        if ($this->isObjectOfSameType($type, $jvalue)) {
            return $jvalue;
        }

        if ($this->isSimpleType($type)) {
            if ($strictChecking && !$this->isFlatType(gettype($jvalue))) {
                throw new JsonMapper_BadTypeException(
                    'JSON value at '.$jsonPath.' must be a '.$type.', '
                    . gettype($jvalue) . ' given',
                    $jsonPath
                );
            }
            return $this->getSimpleValue($jvalue, $type, $strictChecking);
        }

        if ($type instanceof Collection) {
            // verify that the collection accepts only strings and integers
            // as keys. We cannot support for now other type of keys
            // (objects...) with JSON.
            // FIXME: support of other type of keys, with JSON data like
            // [ {"key":..., "value":....}, {"key":..., "value":....}, ...]
            if (!$this->isArrayKeyType($type->getKeyType())) {
                throw new JsonMapper_Exception(
                    'JSON mapper doesn\'t support collection key types other '
                    . 'than string and integers'
                );
            }
            $array = $this->createInstance((string)$type->getFqsen());
            return $this->mapSubArray(
                $jvalue, $jsonPath, $type->getValueType(), $array
            );
        }

        if ($type instanceof Array_) {
            return $this->mapSubArray($jvalue, $jsonPath, $type->getValueType());
        }

        if ($type instanceof Iterable_) {
            return $this->mapSubArray($jvalue, $jsonPath, null, new ArrayObject());
        }

        if ($type instanceof Compound) {
            return $this->mapCompound($jvalue, $type, $jsonPath);
        }

        if ($type instanceof Object_) {
            $className = (string)$type->getFqsen();
            if ($className == '') {
                if (!is_object($jvalue)) {
                    throw new JsonMapper_BadTypeException(
                        "$jsonPath is not an object", $jsonPath
                    );
                }
                return $jvalue;
            }
            if ($className == '\\ArrayObject'
                || is_subclass_of($className, '\\ArrayObject')
            ) {

                $array = $this->createInstance($className);
                return $this->mapSubArray($jvalue, $jsonPath, null, $array);
            }

            if ($this->isFlatType(gettype($jvalue))) {
                // use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($this->bStrictObjectTypeChecking) {
                    throw new JsonMapper_BadTypeException(
                        'JSON value at '.$jsonPath.' must be an object, '
                        . gettype($jvalue) . ' given',
                        $jsonPath
                    );
                }
                $child = $this->createInstance($className, true, $jvalue);
            } else {
                $child = $this->createInstance($className);
                $this->mapObject($jvalue, $child, $jsonPath, $strictChecking);
            }
            return $child;
        }
        throw new JsonMapper_Exception(
            'Expected type "'.$type.'" is not supported by JSON mapper'
        );
    }

    /**
     * Check required properties exist in json
     *
     * @param array  $providedProperties array with json properties
     * @param object $rc                 Reflection class to check
     * @param string $jsonPath           Path to the value into the global JSON
     *                                   structure
     *
     * @throws JsonMapper_Exception
     *
     * @return void
     */
    protected function checkMissingData($providedProperties,
        ReflectionClass $rc, $jsonPath
    ) {
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
                    . ' is missing in JSON data '.$jsonPath
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
     *
     * @return mixed Mapped $array is returned
     */
    public function mapArray($json, $array, $class = null)
    {
        if ($class === null) {
            $type = new Mixed_();
        } else {
            $typeResolver = new TypeResolver();
            $type = $typeResolver->resolve($class);
        }
        if (is_array($array)) {
            return $this->mapSubArray($json, "", $type);
        } else {
            return $this->mapSubArray($json, "", $type, $array);
        }
    }

    /**
     * Map an array
     *
     * @param array  $json     JSON array structure from json_decode()
     * @param string $jsonPath Path to the array into the global JSON structure
     * @param Type   $itemType the expected type for array values
     * @param object $array    ArrayObject that gets filled with
     *                         data from $json
     *
     * @return mixed Mapped $array is returned
     */
    protected function mapSubArray($json, $jsonPath, $itemType = null,
        \Traversable $array = null
    ) {
        if (!is_array($json) && !is_object($json)) {
            throw new JsonMapper_BadTypeException(
                'JSON value at '.$jsonPath.' must be an array, '
                . gettype($json) . ' given',
                $jsonPath
            );
        }
        if (is_object($json)) {
            $json = (array) $json;
        }

        if ($itemType === null || $itemType instanceof Mixed_) {
            if ($array !== null) {
                foreach ($json as $key => $jvalue) {
                    $array[$key] = $jvalue;
                }
                return $array;
            }
            return $json;
        }

        if ($array === null) {
            $array = array();
        }

        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            $jsonValuePath = $jsonPath.'['.$key.']';
            try {
                $value = $this->getPHPValue($jvalue, $jsonValuePath, $itemType);
            }
            catch(JsonMapper_BadTypeException $exception) {
                if ($key > 0) {
                    throw new JsonMapper_BadItemTypeException(
                        'JSON array item "' . $jsonValuePath
                        . '" has not the expected type ' . $itemType
                        . ': '.$exception->getMessage(),
                        $jsonValuePath,
                        $exception->getCode(),
                        $exception
                    );
                } else {
                    throw $exception;
                }
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * maps a value to possible types
     *
     * @param mixed    $jvalue   a JSON value
     * @param Compound $list     a list of possible type for the value
     * @param string   $jsonPath the path of the value into the JSON structure
     *
     * @return mixed the php value
     */
    protected function mapCompound($jvalue, Compound $list, $jsonPath)
    {
        foreach ($list->getIterator() as $type) {
            try {
                $val = $this->getPHPValue($jvalue, $jsonPath, $type, true);
                return $val;
            }
            catch(JsonMapper_UnknownPropertyException $e) {
                // not the good class
                continue;
            }
            catch(JsonMapper_BadPropertyTypeException $e) {
                // we have the good object, but a property has a bad type.
                // let's continue to propagate the exception.
                throw $e;
            }
            catch(JsonMapper_BadItemTypeException $e) {
                // we have an array, it is ok, but an item has a bad type.
                // let's continue to propagate the exception.
                throw $e;
            }
            catch(JsonMapper_BadTypeException $e) {
                // Not the good type, try the next type
                continue;
            }
        }
        throw new JsonMapper_BadTypeException(
            "JSON value is not of one of this types ".$list,
            $jsonPath
        );
    }

    /**
     * Gets the value converted to the given type if possible
     *
     * @param mixed $jvalue         a value from JSON value
     * @param Type  $type           the expected type for the value
     * @param bool  $strictChecking true if type value should be strictly equals
     *                              to the expected type
     *
     * @return mixed
     * @throws JsonMapper_Exception
     */
    protected function getSimpleValue($jvalue, Type $type, $strictChecking = false)
    {
        if (($this->bSimpleTypeLossyDataConversionChecking
            || $this->bStrictSimpleTypeConversionChecking
            || $strictChecking)
            && $this->isLossyDataConversion($type, $jvalue)
        ) {
            $msg = 'Value conversion of JSON value '.
                ' from "'.gettype($jvalue). '" to "'. $type .
                '" is a lossy data conversion';
            if ($this->bStrictSimpleTypeConversionChecking || $strictChecking) {
                throw new JsonMapper_Exception($msg);
            }
            $this->log('info', $msg);
        }
        settype($jvalue, $type);
        return $jvalue;
    }


    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param object  $rc      Reflection class to check
     * @param string  $name    Property name
     * @param Context $context the namespace context for the type resolver
     *
     * @return array First value: if the property exists
     *               Second value: the accessor to use (
     *                 ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     *                 (\phpDocumentor\Reflection\Type or null)
     */
    protected function inspectProperty(ReflectionClass $rc, $name,
        Context $context
    ) {
        //try setter method first
        $setter = 'set' . $this->getCamelCaseName($name);

        $typeResolver = new TypeResolver();
        if ($rc->hasMethod($setter)) {
            $rmeth = $rc->getMethod($setter);
            if ($rmeth->isPublic() || $this->bIgnoreVisibility) {
                $rparams = $rmeth->getParameters();
                if (count($rparams) > 0) {
                    $pclass = $rparams[0]->getClass();
                    if ($pclass !== null) {

                        $resolvedType = $typeResolver->resolve(
                            '\\'.$pclass->getName(),
                            $context
                        );
                        if ($rparams[0]->allowsNull()) {
                            $resolvedType = new Nullable($resolvedType);
                        }
                        return array(
                            true, $rmeth,
                            $resolvedType
                        );
                    }
                }

                $docblock    = $rmeth->getDocComment();
                $annotations = $this->parseAnnotations($docblock);

                if (!isset($annotations['param'][0])) {
                    return array(true, $rmeth, null);
                }
                list($type) = explode(' ', trim($annotations['param'][0]));
                $resolvedType = $typeResolver->resolve($type, $context);
                return array(true, $rmeth, $resolvedType);
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
                $resolvedType = $typeResolver->resolve($type, $context);
                return array(true, $rprop, $resolvedType);
            } else {
                //no setter, private property
                return array(true, null, null);
            }
        }

        //no setter, no property
        return array(false, null, null);
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
        } else if ($class[0] == '\\' && isset($this->classMap[substr($class, 1)])) {
            $class = $this->classMap[substr($class, 1)];
        }

        if ($useParameter) {
            return new $class($parameter);
        } else {
            return (new ReflectionClass($class))->newInstanceWithoutConstructor();
        }
    }

    /**
     * Checks if the given type is a "simple type"
     *
     * @param Type $type type name
     *
     * @return boolean True if it is a simple PHP type
     */
    protected function isSimpleType(Type $type)
    {
        return $type instanceof Types\String_
            || $type instanceof Types\Integer
            || $type instanceof Types\Boolean
            || $type instanceof Types\Float_
            || $type instanceof Types\Scalar;
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param Type  $type  class name of type being required
     * @param mixed $value Some PHP value to be tested
     *
     * @return boolean True if $object has type of $type
     */
    protected function isObjectOfSameType(Type $type, $value)
    {
        if (false === is_object($value) || ! $type instanceof Object_) {
            return false;
        }
        return is_a($value, (string)$type);
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
     * Checks if the given type is nullable
     *
     * @param Type $type type name from the phpdoc param
     *
     * @return boolean True if it is nullable
     */
    protected function isNullable(Type $type)
    {
        if ($type instanceof Nullable) {
            return true;
        }
        if ($type instanceof Compound) {
            foreach ($type->getIterator() as $t) {
                if ($t instanceof Null_) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param Type $type type name from the phpdoc param
     *
     * @return Type The new type value
     */
    protected function removeNullable(Type $type)
    {
        if ($type === null) {
            return null;
        }
        if ($type instanceof Nullable) {
            return $type->getActualType();
        }
        if ($type instanceof Compound) {
            $types = [];
            foreach ($type->getIterator() as $t) {
                if ($t instanceof Null_) {
                    continue;
                }
                $types[] = $t;
            }
            return new Compound($types);
        }
        return $type;
    }

    /**
     * Check if the conversion of a value to the given type will be a lossy data
     * conversion
     *
     * @param Type  $targetType the type to which we want to convert the value
     * @param mixed $value      the value to convert
     *
     * @return boolean true if the conversion will loose data
     */
    public function isLossyDataConversion(Type $targetType, $value)
    {
        if ($targetType instanceof Array_) {
            // ok to convert, all values type will be in an array
            return false;
        }

        $type = gettype($value);
        if ($type == 'array') {
            // we loose all values when converting an array to anything else
            return !($targetType instanceof Array_);
        }

        if ($type == 'boolean') {
            // we will have 0, 1, '1', '', array(true), array(false) so it is ok
            return false;
        }

        if ($type == 'integer') {
            // we will have 123 (double), '123', array(123) so it is ok
            // except when converting to boolean
            if ($targetType instanceof Types\Boolean) {
                return ($value !== 0 && $value !== 1);
            }
            return false;
        }

        if ($type == 'double') {
            if ( $targetType instanceof Types\Scalar) {
                return false;
            }
            if ( $targetType instanceof Types\Integer) {
                // it's ok to convert to integer, except if it has a none zero
                // decimal part
                $whole = floor($value);
                return ($value - $whole) != 0;
            } else if ($targetType instanceof Types\Boolean) {
                // it's ok to convert to boolean if it is 0 or 1
                return ($value !== 0 && $value !== 1);
            }
            return false;
        }
        if ($type == 'string') {
            if ( $targetType instanceof Types\Integer
                || $targetType instanceof Types\Float_
            ) {
                if (is_numeric($value)) {
                    return false;
                }
            } else if ($targetType instanceof Types\Boolean) {
                return ($value !== "0" && $value !== "1");
            }

        }
        if ($targetType instanceof Types\Scalar) {
            return ($type !== 'string');
        }

        return ($type != (string)$targetType);
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
     * Says it the given type match array key types, aka string and/or integers
     *
     * @param Type $keyType the type of array keys
     *
     * @return boolean  true if it is an array key type
     */
    protected function isArrayKeyType(Type $keyType)
    {
        if ($keyType instanceof Types\String_
            || $keyType instanceof Types\Integer
        ) {
            return true;
        }

        if (! $keyType instanceof Compound) {
            return false;
        }
        foreach ($keyType->getIterator() as $item) {
            if (! $item instanceof Types\String_
                && ! $item instanceof Types\Integer
            ) {
                return false;
            }
        }
        return true;
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
