<?php

declare(strict_types=1);
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */

use Psr\Log\LoggerInterface;

/**
 * Simple in-memory logger
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class JsonMapperTest_Logger implements LoggerInterface
{
    public $log = [];

    /**
     * Log a message to the $logger object
     *
     * @param string $level   Logging level
     * @param string $message Text to log
     * @param array  $context Additional information
     */
    public function log($level, $message, array $context = []): void
    {
        $this->log[] = [$level, $message, $context];
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
    }

    public function error(Stringable|string $message, array $context = []): void
    {
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
    }

    public function info(Stringable|string $message, array $context = []): void
    {
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
    }
}
