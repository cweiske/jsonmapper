<?php


abstract class Animal
{
    /** @var string */
    public $name;

    /** @var string */
    public $kind;

    abstract function hasHair();

    abstract function hasScales();

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





