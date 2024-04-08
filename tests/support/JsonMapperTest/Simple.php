<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

/**
 * Unit test helper class for testing property mapping
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapperTest_Simple
{
    public $db;

    /**
     * @var bool
     */
    public $pbool;

    /**
     * @var boolean
     */
    public $pboolean;

    /**
     * @var int
     */
    public $pint;

    /**
     * @var integer
     */
    public $pinteger;

    /**
     * @var int|null
     */
    public $pnullableInt;

    /**
     * @var ?string
     */
    public $pnullableString;

    /**
     * @var float
     */
    public $fl;

    /**
     * @var mixed
     */
    public $mixed;

    /**
     * @var string
     */
    public $str;

    /**
     * This property has no @-var type hint
     */
    public $notype;

    // Intentionally no docblock
    public $nodocblock;

    /**
     * @var string A protected property without a setter method
     */
    protected $protectedStrNoSetter;
    /**
     * @var JsonMapperTest_Simple
     */
    public $simple;

    public $internalData;

    /**
     * Variable name with underscore
     * @var string
     */
    public $under_score;

    /**
     * Variable name with hyphen (-)
     * @var string
     */
    public $hyphenValue;

    /**
     * @var
     */
    public $empty;

    /**
     * @var string
     */
    public $setterPreferredOverProperty;

    /**
     * Value object which needs to be set as an instance (without mapping)
     * @var JsonMapperTest_ValueObject
     */
    public $valueObject;

    public function setSimpleSetterOnlyTypeHint(JsonMapperTest_Simple $s)
    {
        $this->internalData['typehint'] = $s;
    }

    /**
     * @param JsonMapperTest_Simple $s Some test object
     */
    public function setSimpleSetterOnlyDocblock($s)
    {
        $this->internalData['docblock'] = $s;
    }

    public function setSimpleSetterOnlyNoType($s)
    {
        $this->internalData['notype'] = $s;
    }

    public function getProtectedStrNoSetter()
    {
        return $this->protectedStrNoSetter;
    }

    public function setUnderScoreSetter($v)
    {
        $this->internalData['under_score_setter'] = $v;
    }

    public function setHyphenValueSetter($v)
    {
        $this->internalData['hyphen-value-setter'] = $v;
    }

    public function setSetterPreferredOverProperty($v)
    {
        $this->setterPreferredOverProperty = 'set via setter: ' . $v;
    }

    /**
     * @return JsonMapperTest_ValueObject
     */
    public function getValueObject()
    {
        return $this->valueObject;
    }

    /**
     * @param JsonMapperTest_ValueObject $valueObject
     */
    public function setValueObject(JsonMapperTest_ValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }
}
?>
