<?php

declare(strict_types=1);

class PHP80_Array
{
    /**
     * @param JsonMapperTest_ArrayValueForStringProperty[] $files
     */
    public function __construct(
        private array $files,
    ) {
    }

    /**
     * @return JsonMapperTest_ArrayValueForStringProperty[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
