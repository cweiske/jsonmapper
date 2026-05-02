<?php

declare(strict_types=1);

/**
 * Test fixture: an object with DateTime / DateTimeImmutable properties,
 * including nullable, array-of-DateTime, and a user subclass case.
 */
class JsonMapperTest_DateTimes
{
    public ?\DateTime $created = null;
    public ?\DateTimeImmutable $modified = null;
    public ?\DateTimeImmutable $nullable = null;
    /** @var \DateTimeImmutable[] */
    public array $events = [];
}
