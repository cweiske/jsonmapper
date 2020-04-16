<?php

class MapsWithSetters
{
    private $name;
    private $age;

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @maps my_age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAge()
    {
        return $this->age;
    }
}
