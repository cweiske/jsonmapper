<?php

declare(strict_types=1);

namespace Enums;

/**
 * Test fixture: an object that holds an array of BackedEnum values
 * and a nullable BackedEnum property.
 */
class ObjectWithEnumArray
{
    /**
     * @var StringBackedEnum[]
     */
    public array $stringBackedEnums = [];

    /**
     * @var IntBackedEnum[]
     */
    public array $intBackedEnums = [];

    public ?StringBackedEnum $nullableStringEnum = null;
}
