<?php

declare(strict_types=1);
abstract class Animal
{
    public $name;

    public static function determineClass($class, $json)
    {
        if ($json->kind === 'cat') {
            return Cat::class;
        }
        return Fish::class;
    }
}
