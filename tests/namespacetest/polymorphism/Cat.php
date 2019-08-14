<?php





class Cat extends Animal
{
    /** @var int */
    public $legsNumber;

    function hasHair()
    {
        return true;
    }

    function hasScales()
    {
        return false;
    }
}
