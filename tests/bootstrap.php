<?php
if (is_dir(__DIR__ . '/../src/')) {
    set_include_path(
        __DIR__ . '/../src/'
        . PATH_SEPARATOR . get_include_path()
    );
}
include __DIR__ . '/../vendor/autoload.php';

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}
?>
