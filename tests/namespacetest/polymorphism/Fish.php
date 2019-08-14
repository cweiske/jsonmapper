<?php




class Fish extends Animal
{

    /** @var bool */
    public $livesInSaltwater;

    function hasHair()
    {
        return false;
    }

    function hasScales()
    {
        return true;
    }
}
