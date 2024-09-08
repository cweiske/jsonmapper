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
 * @author   Martin Reinfandt <martin.reinfandt@check24.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapperTest_VariadicArray
{
    /**
     * @var DateTime[]
     */
    private $variadicDateTime;

    /**
     * @var int[]
     */
    private $variadicInt;

    public $multipleParamsVal;

    /**
     * @param DateTime[] $items
     *
     * @return self
     */
    public function setVariadicDateTime(DateTime ...$items): self
    {
        $this->variadicDateTime = $items;

        return $this;
    }

    /**
     * @return DateTime[]
     */
    public function getVariadicDateTime(): array
    {
        return $this->variadicDateTime;
    }

    /**
     * @return int[]
     */
    public function getVariadicInt(): array
    {
        return $this->variadicInt;
    }

    /**
     * @param int[] $items
     *
     * @return self
     */
    public function setVariadicInt(int ...$items): self
    {
        $this->variadicInt = $items;

        return $this;
    }

    public function setMultipleParams(array $param, int ...$dummy)
    {
        $this->multipleParamsVal = $param;
    }
}
?>
