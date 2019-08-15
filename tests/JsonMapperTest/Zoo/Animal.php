<?php
abstract class Animal
{
    public $name;

    public static function determineClass($class, $json)
    {
        if ($json->kind === 'cat') {
            return Cat::class;
        } else {
            return Fish::class;
        }
    }
}
