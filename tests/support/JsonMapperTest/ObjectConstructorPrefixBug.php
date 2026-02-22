<?php

class JsonMapperTest_ObjectConstructorPrefixBug
{
    public int $id;
    public DateTime $idDate;

    /**
     * @param DateTime $idDate
     * @param int      $id
     */
    public function __construct(DateTime $idDate, int $id)
    {
        $this->idDate = $idDate;
        $this->id     = $id;
    }
}
