<?php

declare(strict_types=1);

class PHP80_Array
{
    /**
     * @param JsonMapperTest_ArrayValueForStringProperty[] $files
     */
    public function __construct(
        public array $files,
    ) {
    }
}
