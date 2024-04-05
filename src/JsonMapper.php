<?php

declare(strict_types=1);
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

use Psr\Log\LoggerInterface;

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
     * Throw an exception when JSON data contain a property
     * that is not defined in the PHP class
     */
    public bool $bExceptionOnUndefinedProperty = false;

    /**
     * Throw an exception if the JSON data miss a property
     * that is marked with @required in the PHP class
     */
    public bool $bExceptionOnMissingData = false;

    /**
     * If the types of map() parameters shall be checked.
     *
     * You have to disable it if you're using the json_decode "assoc" parameter.
     *
     *     json_decode($str, false)
     */
    public bool $bEnforceMapType = true;

    /**
     * Throw an exception when an object is expected but the JSON contains
     * a non-object type.
     */
    public bool $bStrictObjectTypeChecking = false;

    /**
     * Throw an exception, if null value is found
     * but the type of attribute does not allow nulls.
     */
    public bool $bStrictNullTypes = true;

    /**
     * Allow mapping of private and protected properties.
     */
    public bool $bIgnoreVisibility = false;

    /**
     * Remove attributes that were not passed in JSON,
     * to avoid confusion between them and NULL values.
     */
    public bool $bRemoveUndefinedAttributes = false;

    /**
     * Override class names that JsonMapper uses to create objects.
     * Useful when your setter methods accept abstract classes or interfaces.
     *
     * @var string[]
     */
    public array $classMap = [];

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
    public $undefinedPropertyHandler;

    /**
     * Method to call on each object after deserialization is done.
     *
     * Is only called if it exists on the object.
     */
    public ?string $postMappingMethod = null;

    /**
     * Optional arguments that are passed to the post mapping method
     *
     * @var array<mixed>
     */
    public array $postMappingMethodArguments = [];

    /**
     * PSR-3 compatible logger object
     *
     * @link http://www.php-fig.org/psr/psr-3/
     * @see  setLogger()
     */
    protected ?LoggerInterface $logger = null;

    /**
     * Runtime cache for inspected classes. This is particularly effective if
     * mapArray() is called with a large number of objects
     *
     * @var array<string,mixed> property inspection result cache
     */
    protected array $arInspectedClasses = [];

    /**
     * Map data all data in $json into the given $object instance.
     *
     * @param stdClass|array<mixed>|null $json   JSON object structure
     *                                           from json_decode()
     * @param object|string|null         $object Object to map $json data into
     *
     * @return object Mapped object is returned.
     *
     * @see mapArray()
     *
     * @throws ReflectionException|JsonMapperException
     */
    public function map(
        stdClass|array|null $json,
        object|string|null $object
    ): object {
        // ToDo: refactor $json
        if ($json === null || $this->bEnforceMapType && !$json instanceof stdClass) {
            throw new InvalidArgumentException(
                'JsonMapper::map() requires first argument to be an object, ' . gettype($json) . ' given.' //phpcs:ignore
            );
        }

        if (!is_object($object) && (!is_string($object) || !class_exists($object))) {
            throw new InvalidArgumentException(
                'JsonMapper::map() requires second argument to be an object or existing class name, ' . gettype($object) . ' given.' //phpcs:ignore
            );
        }

        if ($json instanceof stdClass) {
            $json = (array) $json;
        }

        if (is_string($object)) {
            $object = $this->createInstance($object);
        }

        $strClassName = $object::class;
        $rc = new ReflectionClass($object);
        $strNs = $rc->getNamespaceName();
        $providedProperties = [];
        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName((string) $key);
            $providedProperties[$key] = true;

            // Store the property inspection results, so we don't have to do it
            // again for subsequent objects of the same type
            if (!isset($this->arInspectedClasses[$strClassName][$key])) {
                $this->arInspectedClasses[$strClassName][$key]
                    = $this->inspectProperty($rc, $key);
            }

            [$hasProperty, $accessor, $type, $isNullable]
                = $this->arInspectedClasses[$strClassName][$key];

            if (!$hasProperty) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" does not exist'
                        . ' in object of type ' . $strClassName
                    );
                }

                if ($this->undefinedPropertyHandler !== null) {
                    $undefinedPropertyKey = call_user_func(
                        $this->undefinedPropertyHandler,
                        $object,
                        $key,
                        $jvalue
                    );
                    if (is_string($undefinedPropertyKey)) {
                        [$hasProperty, $accessor, $type, $isNullable]
                            = $this->inspectProperty($rc, $undefinedPropertyKey);
                    }
                } else {
                    $this->log(
                        'info',
                        'Property {property} does not exist in {class}',
                        [
                            'property' => $key,
                            'class' => $strClassName,
                        ]
                    );
                }

                if (!$hasProperty) {
                    continue;
                }
            }

            if ($accessor === null) {
                if ($this->bExceptionOnUndefinedProperty) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" has no public setter method'
                        . ' in object of type ' . $strClassName
                    );
                }

                $this->log(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    [
                        'property' => $key,
                        'class' => $strClassName,
                    ]
                );
                continue;
            }

            if ($isNullable || !$this->bStrictNullTypes) {
                if ($jvalue === null) {
                    $this->setProperty($object, $accessor, null);
                    continue;
                }

                $type = $this->removeNullable($type);
            } elseif ($jvalue === null) {
                throw new JsonMapperException(
                    'JSON property "' . $key . '" in class "'
                    . $strClassName . '" must not be NULL'
                );
            }

            $type = $this->getFullNamespace($type, $strNs);
            $type = $this->getMappedType($type, $jvalue);
            if ($type === null || $type === 'mixed') {
                //no given type - simply set the json data
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            }

            if ($this->isObjectOfSameType($type, $jvalue)) {
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            }

            if ($this->isSimpleType($type) &&
                !(is_array($jvalue) && $this->hasVariadicArrayType($accessor)) //phpcs:ignore
            ) {
                if ($this->isFlatType($type)
                    && !$this->isFlatType(gettype($jvalue))
                ) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" in class "'
                        . $strClassName . '" is of type ' . gettype($jvalue) . ' and'
                        . ' cannot be converted to ' . $type
                    );
                }

                settype($jvalue, $type);
                $this->setProperty($object, $accessor, $jvalue);
                continue;
            }

            //FIXME: check if type exists, give detailed error message if not
            if ($type === '') {
                throw new JsonMapperException(
                    'Empty type at property "'
                    . $strClassName . '::$' . $key . '"'
                );
            }

            if (strpos($type, '|')) {
                throw new JsonMapperException(
                    'Cannot decide which of the union types shall be used: '
                    . $type
                );
            }

            $array = null;
            $subtype = null;
            if ($this->isArrayOfType($type)) {
                //array
                $array = [];
                $subtype = substr($type, 0, -2);
            } elseif (str_ends_with($type, ']')) {
                [$proptype, $subtype] = explode('[', substr($type, 0, -1));

                /**
                 * Var is here a classname
                 *
                 * @var class-string $proptype
                 */
                $array = $proptype === 'array' ? [] : $this->createInstance($proptype, false, $jvalue); //phpcs:ignore
            } elseif (is_array($jvalue) && $this->hasVariadicArrayType($accessor)) {
                $array = [];
                $subtype = $type;
            } elseif (is_a($type, 'ArrayAccess', true)) {
                $array = $this->createInstance($type, false, $jvalue);
            }

            if ($array !== null) {
                if (!is_array($jvalue) && $this->isFlatType(gettype($jvalue))) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" must be an array, '
                        . gettype($jvalue) . ' given'
                    );
                }

                $cleanSubtype = $this->removeNullable($subtype);
                $subtype = $this->getFullNamespace($cleanSubtype, $strNs);
                /**
                 * Var is here a classname
                 *
                 * @var class-string $subtype
                 */
                $child = $this->mapArray($jvalue, $array, $subtype, $key);
            } elseif ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($this->bStrictObjectTypeChecking) {
                    throw new JsonMapperException(
                        'JSON property "' . $key . '" must be an object, '
                        . gettype($jvalue) . ' given'
                    );
                }

                /**
                 * Var is here a classname
                 *
                 * @var class-string $type
                 */
                $child = $this->createInstance($type, true, $jvalue);
            } else {
                /**
                 * Var is here a classname
                 *
                 * @var class-string $type
                 */
                $child = $this->createInstance($type, false, $jvalue);
                $this->map($jvalue, $child);
            }

            $this->setProperty($object, $accessor, $child);
        }

        if ($this->bExceptionOnMissingData) {
            $this->checkMissingData($providedProperties, $rc);
        }

        if ($this->bRemoveUndefinedAttributes) {
            $this->removeUndefinedAttributes($object, $providedProperties);
        }

        if ($this->postMappingMethod !== null
            && $rc->hasMethod($this->postMappingMethod)
        ) {
            $refDeserializePostMethod = $rc->getMethod(
                $this->postMappingMethod
            );
            $refDeserializePostMethod->setAccessible(true);
            $refDeserializePostMethod->invoke(
                $object,
                ...$this->postMappingMethodArguments
            );
        }

        return $object;
    }

    /**
     * Map an array
     *
     * @param stdClass|array<mixed> $json       JSON array structure from
     *                                          json_decode()
     * @param mixed                 $array      Array or ArrayObject that gets
     *                                          filled with data from $json
     * @param class-string|null     $class      Class name for children objects.
     *                                          All children will get mapped onto
     *                                          this type.
     *                                          Supports class names and simple types
     *                                          like "string" and nullability
     *                                          "string|null".
     *                                          Pass "null" to not convert any values
     * @param string                $parent_key Defines the key this array belongs to
     *                                          in order to aid debugging.
     *
     * @return mixed Mapped $array is returned
     *
     * @throws ReflectionException
     * @throws JsonMapperException
     */
    public function mapArray(
        stdClass|array $json,
        mixed $array,
        ?string $class = null,
        string $parent_key = ''
    ): mixed {
        if ($json instanceof stdClass) {
            $json = (array) $json;
        }

        $originalClass = $class;
        foreach ($json as $key => $jvalue) {
            $class = $this->getMappedType($originalClass, $jvalue);
            if ($class === null) {
                $array[$key] = $jvalue;
            } elseif ($this->isArrayOfType($class)) {
                /**
                 * Var is here a classname
                 *
                 * @var class-string $classname
                 */
                $classname = substr($class, 0, -2);

                $array[$key] = $this->mapArray(
                    $jvalue,
                    [],
                    $classname
                );
            } elseif ($this->isFlatType(gettype($jvalue))) {
                //use constructor parameter if we have a class
                // but only a flat type (i.e. string, int)
                if ($jvalue === null) {
                    $array[$key] = null;
                } elseif ($this->isSimpleType($class)) {
                    settype($jvalue, $class);
                    $array[$key] = $jvalue;
                } else {
                    /**
                     * Var is here a classname
                     *
                     * @var class-string $class
                     */
                    $array[$key] = $this->createInstance(
                        $class,
                        true,
                        $jvalue
                    );
                }
            } elseif ($this->isFlatType($class)) {
                throw new JsonMapperException(
                    'JSON property "' . ($parent_key ?: '?') . '"'
                    . ' is an array of type "' . $class . '"'
                    . ' but contained a value of type'
                    . ' "' . gettype($jvalue) . '"'
                );
            } elseif (is_a($class, 'ArrayObject', true)) {
                /**
                 * Var is here a classname
                 *
                 * @var class-string $class
                 */
                $array[$key] = $this->mapArray(
                    $jvalue,
                    $this->createInstance($class)
                );
            } else {
                /**
                 * Var is here a classname
                 *
                 * @var class-string $class
                 */
                $array[$key] = $this->map(
                    $jvalue,
                    $this->createInstance($class, false, $jvalue)
                );
            }
        }

        return $array;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger PSR-3 compatible logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Convert a type name to a fully namespaced type name.
     *
     * @param string|null $type  Type name (simple type or class name)
     * @param string      $strNs Base namespace that gets prepended to the type name
     *
     * @return string|null Fully-qualified type name with namespace
     */
    protected function getFullNamespace(?string $type, string $strNs): ?string
    {
        if ($type === null || $type === '' || $type[0] === '\\' || $strNs === '') {
            return $type;
        }

        [$first] = explode('[', $type, 2);
        if ($this->isSimpleType($first)) {
            return $type;
        }

        //create a full qualified namespace
        return '\\' . $strNs . '\\' . $type;
    }

    /**
     * Check required properties exist in json
     *
     * @param array<string,mixed> $providedProperties array with json properties
     * @param ReflectionClass     $rc                 Reflection class to check
     *
     * @return void
     *
     * @throws JsonMapperException
     * @throws ReflectionException
     */
    protected function checkMissingData(
        array $providedProperties,
        ReflectionClass $rc,
    ): void {
        foreach ($rc->getProperties() as $property) {
            $rprop = $rc->getProperty($property->name);
            $docblock = $rprop->getDocComment();
            $annotations = static::parseAnnotations($docblock);
            if (!isset($annotations['required'])) {
                continue;
            }

            if (isset($providedProperties[$property->name])) {
                continue;
            }

            throw new JsonMapperException(
                'Required property "' . $property->name . '" of class '
                . $rc->getName()
                . ' is missing in JSON data'
            );
        }
    }

    /**
     * Remove attributes from object that were not passed in JSON data.
     *
     * This is to avoid confusion between those that were actually passed
     * as NULL, and those that weren't provided at all.
     *
     * @param object              $object             Object to remove properties
     *                                                from
     * @param array<string,mixed> $providedProperties Array with JSON properties
     *
     * @return void
     */
    protected function removeUndefinedAttributes(
        object $object,
        array $providedProperties,
    ): void {
        foreach (array_keys(get_object_vars($object)) as $propertyName) {
            if (!isset($providedProperties[$propertyName])) {
                unset($object->{$propertyName});
            }
        }
    }

    /**
     * Try to find out if a property exists in a given class.
     * Checks property first, falls back to setter method.
     *
     * @param ReflectionClass $rc   Reflection class to check
     * @param string          $name Property name
     *
     * @return array<int,mixed> First value: if the property exists
     *               Second value: the accessor to use (
     *                 ReflectionMethod or ReflectionProperty, or null)
     *               Third value: type of the property
     *               Fourth value: if the property is nullable
     */
    protected function inspectProperty(ReflectionClass $rc, string $name): array
    {
        //try setter method first
        $setter = 'set' . $this->getCamelCaseName($name);

        if ($rc->hasMethod($setter)) {
            $rmeth = $rc->getMethod($setter);
            if ($rmeth->isPublic() || $this->bIgnoreVisibility) {
                $isNullable = false;
                $rparams = $rmeth->getParameters();
                if (count($rparams) > 0) {
                    $isNullable = $rparams[0]->allowsNull();
                    $ptype = $rparams[0]->getType();
                    if ($ptype !== null) {
                        $typeName = $this->stringifyReflectionType($ptype);
                        //allow overriding an "array" type hint
                        // with a more specific class in the docblock
                        if ($typeName !== 'array') {
                            return [
                                true, $rmeth,
                                $typeName,
                                $isNullable,
                            ];
                        }
                    }
                }

                $docblock = $rmeth->getDocComment();
                $annotations = static::parseAnnotations($docblock);

                if (!isset($annotations['param'][0])) {
                    return [true, $rmeth, null, $isNullable];
                }

                [$type] = explode(' ', trim((string) $annotations['param'][0]));
                return [true, $rmeth, $type, $this->isNullable($type)];
            }
        }

        //now try to set the property directly
        //we have to look it up in the class hierarchy
        $class = $rc;
        $rprop = null;
        do {
            if ($class->hasProperty($name)) {
                $rprop = $class->getProperty($name);
            }
        } while ($rprop === null && $class = $class->getParentClass());

        if ($rprop === null) {
            //case-insensitive property matching
            foreach ($rc->getProperties() as $p) {
                if ((strcasecmp($p->name, $name) === 0)) {
                    $rprop = $p;
                    $class = $rc;
                    break;
                }
            }
        }

        if ($rprop !== null) {
            if ($rprop->isPublic() || $this->bIgnoreVisibility) {
                $docblock = $rprop->getDocComment();
                if (PHP_VERSION_ID >= 80000 && $docblock === false
                    && $class instanceof ReflectionClass
                    && $class->hasMethod('__construct')
                ) {
                    $docblock = $class->getMethod('__construct')->getDocComment();
                }

                $annotations = static::parseAnnotations($docblock);

                if (!isset($annotations['var'][0])) {
                    if (PHP_VERSION_ID >= 80000 && $rprop->hasType()
                        && isset($annotations['param'])
                    ) {
                        foreach ($annotations['param'] as $param) {
                            $param = (string) $param;
                            if (str_contains($param, '$' . $rprop->getName())) {
                                [$type] = explode(' ', $param);
                                return [
                                    true, $rprop, $type, $this->isNullable($type),
                                ];
                            }
                        }
                    }

                    // If there is no annotations (higher priority) inspect
                    // if there's a scalar type being defined
                    $rPropType = $rprop->getType();
                    if ($rPropType instanceof ReflectionType) {
                        $propTypeName = $this->stringifyReflectionType($rPropType);
                        if ($this->isSimpleType($propTypeName)) {
                            return [
                                true,
                                $rprop,
                                $propTypeName,
                                $rPropType->allowsNull(),
                            ];
                        }

                        return [
                            true,
                            $rprop,
                            '\\' . ltrim($propTypeName, '\\'),
                            $rPropType->allowsNull(),
                        ];
                    }

                    return [true, $rprop, null, false];
                }

                //support "@var type description"
                [$type] = explode(' ', (string) $annotations['var'][0]);

                return [true, $rprop, $type, $this->isNullable($type)];
            }

            //no setter, private property
            return [true, null, null, false];
        }

        //no setter, no property
        return [false, null, null, false];
    }

    /**
     * Removes - and _ and makes the next letter uppercase
     *
     * @param string $name Property name
     *
     * @return string CamelCasedVariableName
     */
    protected function getCamelCaseName(string $name): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(str_replace(['_', '-'], ' ', $name))
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
    protected function getSafeName(string $name): string
    {
        if (str_contains($name, '-')) {
            return $this->getCamelCaseName($name);
        }

        return $name;
    }

    /**
     * Set a property on a given object to a given value.
     *
     * Checks if the setter or the property are public are made before
     * calling this method.
     *
     * @param object                              $object   Object to set property on
     * @param ReflectionMethod|ReflectionProperty $accessor Reflection accessor
     * @param mixed                               $value    Value of property
     *
     * @return void
     *
     * @throws ReflectionException
     */
    protected function setProperty(
        object $object,
        ReflectionMethod|ReflectionProperty $accessor,
        mixed $value
    ): void {
        if (!$accessor->isPublic() && $this->bIgnoreVisibility) {
            $accessor->setAccessible(true);
        }

        if ($accessor instanceof ReflectionProperty) {
            $accessor->setValue($object, $value);
        } elseif (is_array($value) && $this->hasVariadicArrayType($accessor)) {
            $accessor->invoke($object, ...$value);
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
     * @param class-string $class        Class name to instantiate
     * @param boolean      $useParameter Pass $parameter to the constructor or not
     * @param mixed        $jvalue       Constructor parameter (the json value)
     *
     * @return object Freshly created object
     *
     * @throws ReflectionException
     */
    protected function createInstance(
        string $class,
        bool $useParameter = false,
        mixed $jvalue = null
    ): object {
        if ($useParameter) {
            if (PHP_VERSION_ID >= 80100
                && is_subclass_of($class, BackedEnum::class)
            ) {
                return $class::from($jvalue);
            }

            return new $class($jvalue);
        }

        $reflectClass = new ReflectionClass($class);
        $constructor = $reflectClass->getConstructor();
        if ($constructor === null
            || $constructor->getNumberOfRequiredParameters() > 0
        ) {
            return $reflectClass->newInstanceWithoutConstructor();
        }

        return $reflectClass->newInstance();
    }

    /**
     * Get the mapped class/type name for this class.
     * Returns the incoming classname if not mapped.
     *
     * @param string|null $type   Type name to map
     * @param mixed       $jvalue Constructor parameter (the json value)
     *
     * @return string|null The mapped type/class name
     */
    protected function getMappedType(?string $type, mixed $jvalue = null): ?string
    {
        if (isset($this->classMap[$type])) {
            $target = $this->classMap[$type];
        } elseif (is_string($type) && $type !== '' && $type[0] === '\\'
            && isset($this->classMap[substr($type, 1)])
        ) {
            $target = $this->classMap[substr($type, 1)];
        } else {
            $target = null;
        }

        if ($target) {
            return is_callable($target) ? $target($type, $jvalue) : $target;
        }

        return $type;
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
    protected function isSimpleType(string $type): bool
    {
        return $type === 'string'
            || $type === 'boolean' || $type === 'bool'
            || $type === 'integer' || $type === 'int'
            || $type === 'double' || $type === 'float'
            || $type === 'array' || $type === 'object'
            || $type === 'mixed';
    }

    /**
     * Checks if the object is of this type or has this type as one of its parents
     *
     * @param string $type  class name of type being required
     * @param mixed  $value Some PHP value to be tested
     *
     * @return boolean True if $object has type of $type
     */
    protected function isObjectOfSameType(string $type, mixed $value): bool
    {
        if (is_object($value) === false) {
            return false;
        }

        return $value instanceof $type;
    }

    /**
     * Checks if the given type is a type that is not nested
     * (simple type except array, object and mixed)
     *
     * @param string $type type name from gettype()
     *
     * @return boolean True if it is a non-nested PHP type
     *
     * @see isSimpleType()
     */
    protected function isFlatType(string $type): bool
    {
        return $type === 'NULL'
            || $type === 'string'
            || $type === 'boolean' || $type === 'bool'
            || $type === 'integer' || $type === 'int'
            || $type === 'double' || $type === 'float';
    }

    /**
     * Returns true if type is an array of elements
     * (bracket notation)
     *
     * @param string $strType type to be matched
     *
     * @return boolean
     */
    protected function isArrayOfType(string $strType): bool
    {
        return str_ends_with($strType, '[]');
    }

    /**
     * Returns true if accessor is a method and has only one parameter
     * which is variadic.
     *
     * @param ReflectionMethod|ReflectionProperty|null $accessor accessor to set
     *                                                           value
     *
     * @return boolean
     */
    protected function hasVariadicArrayType(
        null|ReflectionMethod|ReflectionProperty $accessor
    ): bool {
        if (!$accessor instanceof ReflectionMethod) {
            return false;
        }

        $parameters = $accessor->getParameters();

        if (count($parameters) !== 1) {
            return false;
        }

        $parameter = $parameters[0];

        return $parameter->isVariadic();
    }

    /**
     * Checks if the given type is nullable
     *
     * @param string $type type name from the phpdoc param
     *
     * @return boolean True if it is nullable
     */
    protected function isNullable(string $type): bool
    {
        return stripos('|' . $type . '|', '|null|') !== false;
    }

    /**
     * Remove the 'null' section of a type
     *
     * @param string|null $type type name from the phpdoc param
     *
     * @return string|null The new type value
     */
    protected function removeNullable(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        return substr(
            str_ireplace('|null|', '|', '|' . $type . '|'),
            1,
            -1
        );
    }

    /**
     * Get a string representation of the reflection type.
     * Required because named, union and intersection types need to be handled.
     *
     * @param ReflectionType $type Native PHP type
     *
     * @return string "foo|bar"
     */
    protected function stringifyReflectionType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionNamedType) {
            return ($type->isBuiltin() ? '' : '\\') . $type->getName();
        }

        if (!method_exists($type, 'getTypes')) {
            throw new InvalidArgumentException(
                'ReflectionType does not have getTypes() method'
            );
        }

        return implode(
            '|',
            array_map(
                static fn (ReflectionNamedType $type): string =>
                    ($type->isBuiltin() ? '' : '\\') . $type->getName(),
                $type->getTypes()
            )
        );
    }

    /**
     * Copied from PHPUnit 3.7.29, Util/Test.php
     *
     * @param string|bool $docblock Full method docblock
     *
     * @return array<mixed> Array of arrays.
     *               Key is the "@"-name like "param",
     *               each value is an array of the rest of the @-lines
     */
    protected static function parseAnnotations(string|bool $docblock): array
    {
        if (is_bool($docblock)) {
            return [];
        }

        $annotations = [];
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
     * @param string       $level   Logging level
     * @param string       $message Text to log
     * @param array<mixed> $context Additional information
     *
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message, $context);
        }
    }
}
