<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Apimatic
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://www.apimatic.io/
 */
namespace apimatic\jsonmapper;

/**
 * Data class to hold the groups of multiple types.
 *
 * @category Apimatic
 * @package  JsonMapper
 * @author   Asad Ali <asad.ali@apimatic.io>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://www.apimatic.io/
 */
class TypeCombination
{
    /**
     * String format of this typeCombinator group.
     *
     * @var string
     */
    private $_format;

    /**
     * Name of this typeCombinator group i.e. oneOf/anyOf.
     *
     * @var string
     */
    private $_groupName;

    /**
     * Array of string types or TypeCombination objects
     *
     * @var array
     */
    private $_types;

    /**
     * A list of factory methods to deserialize the given object,
     * for one of the wrapped types in this group
     *
     * @var string[]
     */
    private $_deserializers;

    /**
     * Private constructor for TypeCombination class
     *
     * @param string   $format        string format value
     * @param string   $groupName     group name value
     * @param array    $types         types value
     * @param string[] $deserializers deserializers value
     */
    private function __construct($format, $groupName, $types, $deserializers)
    {
        $this->_format = $format;
        $this->_groupName = $groupName;
        $this->_types = $types;
        $this->_deserializers = $deserializers;
    }

    /**
     * String format of this typeCombinator group.
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Name of this typeCombinator group i.e. oneOf/anyOf/array/map.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->_groupName;
    }

    /**
     * Array of string types or TypeCombination objects
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * A list of factory methods to deserialize the given object,
     * for one of the wrapped types in this group
     *
     * @return string[]
     */
    public function getDeserializers()
    {
        return $this->_deserializers;
    }

    /**
     * Extract type info like: isMap, isArray, and inner type for maps/arrays.
     *
     * @param string $type Type to be checked and extracted for information.
     *
     * @return array An array with type info in the format:
     *               (bool isMap, bool isArray, string $internalType).
     */
    public static function extractTypeInfo($type)
    {
        $mapStart = 'array<string,';
        // Check if container is array or map?
        $isMap = substr($type, -1) == '>' && strpos($type, $mapStart) === 0;
        $isArray = substr($type, -2) == '[]';
        // Extracting inner type for arrays/maps
        // Inner type will be same as actual type for non-container type
        $innerType = $isMap ? substr($type, strlen($mapStart), -1)
            : ($isArray ? substr($type, 0, -2) : $type);
        return [$isMap, $isArray, $innerType];
    }

    /**
     * Wrap the given typeGroup string in the TypeCombination class,
     * i.e. getTypes() method will return all the grouped types,
     * while deserializing factory methods can be obtained by
     * getDeserializers() and group name can be obtained from getGroupName()
     *
     * @param string   $typeGroup     Format of multiple types i.e. oneOf(int,bool)[]
     *                                onyOf(int[],bool,anyOf(string,float)[],...),
     *                                array<string,oneOf(int,float)[]>, here []
     *                                represents array types, and array<string,T>
     *                                represents map types, oneOf/anyOf are group
     *                                names, while default group name is anyOf.
     * @param string[] $deserializers Callable factory methods for the property
     *
     * @return TypeCombination
     */
    public static function generateTypeCombination($typeGroup, $deserializers)
    {
        $groupName = 'anyOf';
        $start = strpos($typeGroup, '(');
        $end = strrpos($typeGroup, ')');
        if ($start !== false && $end !== false) {
            list($isMap, $isArray, $innerType) = self::extractTypeInfo($typeGroup);
            if ($isMap || $isArray) {
                return self::_createTypeGroup(
                    $isMap ? 'map' : 'array',
                    $innerType,
                    $deserializers
                );
            }
            $name = substr($typeGroup, 0, $start);
            $groupName = empty($name) ? $groupName : $name;
            $typeGroup = substr($typeGroup, $start + 1, -1);
        }
        $format = "($typeGroup)";
        $types = [];
        $type = '';
        $groupCount = 0;
        foreach (str_split($typeGroup) as $c) {
            if ($c == '(' || $c == '<') {
                $groupCount++;
            }
            if ($c == ')' || $c == '>') {
                $groupCount--;
            }
            if ($c == ',' && $groupCount == 0) {
                self::_insertType($types, $type, $deserializers);
                $type = '';
                continue;
            }
            $type .= $c;
        }
        self::_insertType($types, $type, $deserializers);
        return new self($format, $groupName, $types, $deserializers);
    }

    /**
     * Creates a TypeCombination object with the given name and inner
     * types group that must be another typeCombination object
     *
     * @param string   $name          Group name for the outer typeCombination
     *                                object.
     * @param string   $innerGroup    typeGroup to be created and inserted
     * @param string[] $deserializers deserializer for the type group
     *
     * @return TypeCombination
     */
    private static function _createTypeGroup($name, $innerGroup, $deserializers)
    {
        $format = $name == 'map' ? "array<string,$innerGroup>" : $innerGroup . '[]';
        return new self(
            $format,
            $name,
            [self::generateTypeCombination($innerGroup, $deserializers)],
            $deserializers
        );
    }

    /**
     * Insert the type in the types array which is passed by reference,
     * Also check if type is not empty
     *
     * @param array    $types         types array reference
     * @param string   $type          type to be inserted
     * @param string[] $deserializers deserializer for the type group
     *
     * @return void
     */
    private static function _insertType(&$types, $type, $deserializers)
    {
        if (strpos($type, '(') !== false && strrpos($type, ')') !== false) {
            // If type is Grouped, creating TypeCombination instance for it
            $type = self::generateTypeCombination($type, $deserializers);
        }
        if (!empty($type)) {
            array_push($types, $type);
        }
    }
}
