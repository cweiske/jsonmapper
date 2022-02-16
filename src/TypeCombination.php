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
     * @param string   $groupName     group name value
     * @param array    $types         types value
     * @param string[] $deserializers deserializers value
     */
    private function __construct($groupName, $types, $deserializers)
    {
        $this->_groupName = $groupName;
        $this->_types = $types;
        $this->_deserializers = $deserializers;
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
     * Converts the given typeCombination into its string format.
     *
     * @param TypeCombination|string $type  Combined type/Single type.
     * @param string                 $start string to be appended in the start
     * @param string                 $end   string to be appended in the end
     *
     * @return string
     */
    public static function generateTypeString($type, $start = '', $end = '')
    {
        if (is_string($type)) {
            return $type;
        }
        if ($type->getGroupName() == 'array') {
            return self::generateTypeString(
                $type->getTypes()[0],
                $start,
                '[]' . $end
            );
        }
        if ($type->getGroupName() == 'map') {
            return self::generateTypeString(
                $type->getTypes()[0],
                $start . 'array<string,',
                '>' . $end
            );
        }
        $flatten = [];
        array_map(
            function ($a) use (&$flatten) {
                $flatten[] = self::generateTypeString($a);
            },
            $type->getTypes()
        );
        return "$start(" . join(',', $flatten) . ")$end";
    }

    /**
     * Wrap the given typeGroup string in the TypeCombination class,
     * i.e. getTypes() method will return all the grouped types,
     * while deserializing factory methods can be obtained by
     * getDeserializers() and group name can be obtained from getGroupName()
     *
     * @param string    $typeGroup    Format of multiple types i.e. oneOf(int,bool)[]
     *                                onyOf(int[],bool,anyOf(string,float)[],...),
     *                                array<string,oneOf(int,float)[]>, here []
     *                                represents array types, and array<string,T>
     *                                represents map types, oneOf/anyOf are group
     *                                names, while default group name is anyOf.
     * @param string[]  $deserializer Callable factory methods for the property
     * @param int|false $start        Start index of types in group, default: false.
     * @param int|false $end          Ending index of types in group, default: false.
     *
     * @return TypeCombination
     */
    public static function generateTypeCombination(
        $typeGroup,
        $deserializer,
        $start = false,
        $end = false
    ) {
        $groupName = 'anyOf';
        $start = $start == false ? strpos($typeGroup, '(') : $start;
        $end = $end == false ? strrpos($typeGroup, ')') : $end;
        if ($start !== false && $end !== false) {
            if (substr($typeGroup, -2) == '[]') {
                return self::_createTypeGroup(
                    'array',
                    substr($typeGroup, 0, -2),
                    $deserializer
                );
            }
            if (substr($typeGroup, -1) == '>' 
                && strpos($typeGroup, 'array<string,') === 0
            ) {
                return self::_createTypeGroup(
                    'map',
                    substr($typeGroup, strlen('array<string,'), -1),
                    $deserializer
                );
            }
            $name = substr($typeGroup, 0, $start);
            $groupName = empty($name) ? $groupName : $name;
            $typeGroup = substr($typeGroup, $start + 1, -1);
        }
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
                self::_insertType($types, $type, $deserializer);
                $type = '';
                continue;
            }
            $type .= $c;
        }
        self::_insertType($types, $type, $deserializer);
        return new self($groupName, $types, $deserializer);
    }

    /**
     * Creates a TypeCombination object with the given name and inner
     * types group that must be another typeCombination object
     *
     * @param string   $name           Group name for the outer typeCombination
     *                                 object.
     * @param string   $innerTypeGroup typeGroup to be created and inserted
     * @param string[] $deserializers  deserializer for the type group
     *
     * @return TypeCombination
     */
    private static function _createTypeGroup($name, $innerTypeGroup, $deserializers)
    {
        return new self(
            $name,
            [self::generateTypeCombination($innerTypeGroup, $deserializers)],
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
        $start = strpos($type, '(');
        $end = strrpos($type, ')');
        if ($start !== false && $end !== false) {
            $type = self::generateTypeCombination(
                $type,
                $deserializers,
                $start,
                $end
            );
        }
        if (!empty($type)) {
            array_push($types, $type);
        }
    }
}
