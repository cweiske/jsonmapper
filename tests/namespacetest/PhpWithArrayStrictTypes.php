<?php

declare(strict_types=1);

namespace namespacetest;

class PhpWithArrayStrictTypes
{
    public int $id;

    /**
     * @var \namespacetest\model\User[]
     */
    public array $users;

    public array $simpleArray;
}
