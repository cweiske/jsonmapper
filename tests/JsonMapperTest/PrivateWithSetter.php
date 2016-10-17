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
     * @var int
     */
    private $privatePropertyPrivateSetter = 0;

    private $_internal = array();

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
     * @param int $ppps
     *
     * @return $this
     */
    private function setPrivatePropertyPrivateSetter($ppps)
    {
        $this->privatePropertyPrivateSetter = $ppps;
        return $this;
    }

    /**
     * There is no property with this name, only a setter method
     *
     * @param int $ps
     *
     * @return $this
     */
    private function setPrivateSetter($ps)
    {
        $this->_internal['ps'] = $ps;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrivateProperty()
    {
        return $this->privateProperty;
    }

    /**
     * @return int
     */
    public function getPrivateNoSetter()
    {
        return $this->privateNoSetter;
    }

    /**
     * @return int
     */
    public function getPrivatePropertyPrivateSetter()
    {
        return $this->privatePropertyPrivateSetter;
    }

    public function getPrivateSetter()
    {
        return $this->_internal['ps'];
    }
}
?>
