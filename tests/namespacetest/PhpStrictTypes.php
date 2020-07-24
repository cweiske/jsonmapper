<?php

declare(strict_types=1);

namespace namespacetest;

use namespacetest\model\User;

class PhpStrictTypes
{
    public int $id;

    public User $importedNs;

    public \othernamespace\Foo $otherNs;

    public $withoutType;

    public ?string $nullable;

    /**
     * @var \othernamespace\Foo[] Array containing foos.
     */
    public array $fooArray;
}
