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

use Exception;

/**
 * Simple exception
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <christian.weiske@netresearch.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://www.netresearch.de/
 */
class JsonMapperException extends Exception
{
    /**
     * Exception for discarded comments setting in configuration.
     * 
     * @param array $concernedKeys Keys (PHP directives) with issues.
     * 
     * @return JsonMapperException
     */
    static function commentsDisabledInConfigurationException($concernedKeys)
    {
        return new self(
            "Comments cannot be discarded in the configuration file i.e." .
            " the php.ini file; doc comments are a requirement for JsonMapper." .
            " Following configuration keys must have a value set to \"1\": " .
            implode(", ", $concernedKeys) . "."
        );
    }

    /**
     * Exception for non-existent key in an object.
     * 
     * @param string $key             The missing key/property.
     * @param string $strClassName    The class in which the key is missing.
     * @param bool   $setterException Raise an exception specific to 
     *                                missing a setter within the class for 
     *                                the specified string.
     * 
     * @return JsonMapperException
     */
    static function undefinedPropertyException(
        $key,
        $strClassName,
        $setterException = false
    ) {
        if ($setterException === true) {
            return new self(
                "JSON property '$key' has no public setter method " .
                "in object of type '$strClassName'"
            );
        }

        return new self(
            "JSON property '$key' does not exist " .
            "in object of type '$strClassName'"
        );
    }

    /**
     * Exception for non-existent key in an object.
     * 
     * @param string $key          The property missing type.
     * @param string $strClassName The class in which the property is missing type.
     * 
     * @return JsonMapperException
     */
    static function missingTypePropertyException($key, $strClassName)
    {
        return new self("Empty type at property '$strClassName::$$key'");
    }

    /**
     * Exception for an unCallable Factory Method.
     * 
     * @param string $factoryMethod The concerned factory method.
     * @param string $strClassName  Related class name.
     * 
     * @return JsonMapperException
     */
    static function unCallableFactoryMethodException($factoryMethod, $strClassName)
    {
        return new self(
            "Factory method '$factoryMethod' referenced by ".
            "'$strClassName' is not callable."
        );
    }

    /**
     * Exception when it is not possible to map an object to a specific type.
     * 
     * @param string $typeName  Name of type to map json object on.
     * @param string $typeGroup Group name of the type provided.
     * @param string $value     JSON string.
     * 
     * @return JsonMapperException
     */
    static function unableToMapException($typeName, $typeGroup, $value)
    {
        return new self(
            "Unable to map $typeName: "
            . TypeCombination::generateTypeString($typeGroup) 
            . " on: " . json_encode($value)
        );
    }

    /**
     * Exception raised when a json object maps to more 
     * than one type within the types specified within OneOf.
     * 
     * @param string $matchedType First type.
     * @param string $mappedWith  Second type.
     * @param string $json        JSON string.
     * 
     * @return JsonMapperException
     */
    static function moreThanOneOfException($matchedType, $mappedWith, $json)
    {
        return new self(
            "Cannot map more than OneOf { " .
            TypeCombination::generateTypeString($matchedType) . " and " .
            TypeCombination::generateTypeString($mappedWith) . " } on: " .
            json_encode($json)
        );
    }

    /**
     * JSON does not match any of the types provided.
     * 
     * @param string $type The type JSON could not be mapped to.
     * @param string $json JSON string.
     * 
     * @return JsonMapperException
     */
    static function cannotMapAnyOfException($type, $json)
    {
        return new self(
            "Unable to map AnyOf " .
            TypeCombination::generateTypeString($type) .
            " on: " . json_encode($json)
        );
    }

    /**
     * A property marked as required was missing in the object provided.
     * 
     * @param object $property Concerned property.
     * @param object $rc       The class in which the property was not found.
     * 
     * @return JsonMapperException
     */
    static function requiredPropertyMissingException($property, $rc)
    {
        return new self(
            "Required property '$property->name' of class " .
            "'{$rc->getName()}' is missing in JSON data"
        );
    }

    /**
     * No arguments provided or provided arguments were less than required.
     * 
     * @param string $class                  The concerned class.
     * @param object $ctor                   The concerned class's constructor.
     * @param bool   $noArguments            Boolean to check if we have to raise 
     *                                       an exception for no arguments/less 
     *                                       arguments provided.
     * @param array  $ctorRequiredParamsName Required parameters array.
     * 
     * @return JsonMapperException
     */
    static function noArgumentsException(
        $class,
        $ctor = null,
        $noArguments = false,
        $ctorRequiredParamsName = null
    ) {
        if ($noArguments === true) {
            return new self(
                "$class class requires {$ctor->getNumberOfRequiredParameters()} "
                . "arguments in constructor but none provided"
            );
        }

        return new self(
            "Could not find required constructor arguments for $class: "
            . implode(", ", $ctorRequiredParamsName)
        );
    }
}
