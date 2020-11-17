<?php

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Asserts that a variable is of a given type.
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit_Framework_ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @codeCoverageIgnore
     */
    public static function assertIsType($expected, $actual, $message = '')
    {
        $constraint = class_exists(\PHPUnit\Framework\Constraint\IsType::class)
            ? new \PHPUnit\Framework\Constraint\IsType($expected)
            : new \PHPUnit_Framework_Constraint_IsType($expected);

        static::assertThat($actual, $constraint, $message);
    }
}
