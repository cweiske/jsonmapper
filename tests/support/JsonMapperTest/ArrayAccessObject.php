<?php

declare(strict_types=1);

require_once __DIR__ . '/ValueObject.php';

class JsonMapperTest_ArrayAccessObject implements \ArrayAccess
{
    /**
     * @var int
     */
    public $eins;

    /**
     * @var string
     */
    public $zwei;

    /**
     * @var JsonMapperTest_ValueObject
     */
    public $valueObject;

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->$offset ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}
