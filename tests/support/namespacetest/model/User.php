<?php

declare(strict_types=1);

namespace namespacetest\model;

class User
{
    /**
     * @var string
     */
    public $name;

    public function __construct($name = null)
    {
        $this->name = $name;
    }
}
