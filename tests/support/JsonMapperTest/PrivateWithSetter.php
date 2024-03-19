<?php

class JsonMapperTest_PrivateWithSetter
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

    /**
     * @var int
     */
    private $privatePropertySetterWithoutDoc = 0;

    /**
     * @var int|null
     */
    private $privatePropertyNullableSetterWithoutDoc = 0;


    /**
     * @var JsonMapperTest_Simple[]
     */
    private $privateArrayOfSimple;

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

    public function setPrivatePropertySetterWithoutDoc(int $privateProperty)
    {
        $this->privatePropertySetterWithoutDoc = $privateProperty;
        return $this;
    }

    public function setPrivatePropertyNullableSetterWithoutDoc(int $privateProperty = null)
    {
        $this->privatePropertyNullableSetterWithoutDoc = $privateProperty;
        return $this;
    }

    /**
     * @param JsonMapperTest_Simple[] $simples
     */
    public function setPrivateArrayOfSimple(array $simples)
    {
        $this->privateArrayOfSimple = $simples;
        return $this;
    }

    /**
     * @return JsonMapperTest_Simple[]
     */
    public function getPrivateArrayOfSimple()
    {
        return $this->privateArrayOfSimple;
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

    /**
     * @return int
     */
    public function getPrivatePropertySetterWithoutDoc()
    {
        return $this->privatePropertySetterWithoutDoc;
    }

    /**
     * @return int|null
     */
    public function getPrivatePropertyNullableSetterWithoutDoc()
    {
        return $this->privatePropertyNullableSetterWithoutDoc;
    }

    public function getPrivateSetter()
    {
        return $this->_internal['ps'];
    }
}
?>
