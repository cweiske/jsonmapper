<?php

class PrivateWithSetter
{
    /**
     * @var int
     */
    private $privateProperty = 0;

    /**
     * @var int
     */
    private $privateNoSetter = 0;

    /**
     * @param int $privateProperty
     *
     * @return $this
     */
    public function setPrivateProperty($privateProperty)
    {
        $this->privateProperty = $privateProperty;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrivateProperty()
    {
        return $this->privateProperty;
    }
}
