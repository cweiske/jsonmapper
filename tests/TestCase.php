<?php

trait TestCase
{
    /**
     * Asserts that a variable is of a given type.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @codeCoverageIgnore
     */
    private static function assertInternalType($expected, $actual, $message = '')
    {
        static::assertThat(
            $actual,
            new \PHPUnit\Framework\Constraint\IsType($expected),
            $message
        );
    }
}
