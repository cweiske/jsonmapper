<?php

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Asserts that a variable is of a given type.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @codeCoverageIgnore
     */
    public static function assertInternalType($expected, $actual, $message = '')
    {
        static::assertThat(
            $actual,
            new \PHPUnit\Framework\Constraint\IsType($expected),
            $message
        );
    }
}
