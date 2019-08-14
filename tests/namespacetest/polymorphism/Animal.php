<?php


namespace namespacetest\polimorphism;


use InvalidArgumentException;

abstract class Animal
{
    /** @var string */
    public $name;

    /** @var string */
    public $kind;

    abstract function hasHair(): bool;

    abstract function hasScales(): bool;

    public static function jsonMapper($class, $json)
    {
        if (!property_exists($json, 'kind')) {
            throw new InvalidArgumentException("Required parameter 'type' not found.");
        }

        switch ($json->kind) {
            case "cat":
                return Cat::class;
            case "fish":
                return Fish::class;
        }
        throw new InvalidArgumentException('Unrecognized Field type.');
    }
}

class Cat extends Animal
{
    /** @var int */
    public $legsNumber;

    function hasHair(): bool
    {
        return true;
    }

    function hasScales(): bool
    {
        return false;
    }
}

class Fish extends Animal
{

    /** @var bool */
    public $livesInSaltwater;

    function hasHair(): bool
    {
        return false;
    }

    function hasScales(): bool
    {
        return true;
    }
}

class Zoo
{
    /** @var string */
    public $name;

    /** @var Animal[] */
    public $animals;

}
