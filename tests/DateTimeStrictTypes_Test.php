<?php

declare(strict_types=1);

/**
 * Unit tests for JsonMapper's DateTime support when
 * bStrictObjectTypeChecking is enabled.
 *
 * Regression coverage for https://github.com/cweiske/jsonmapper/issues/240
 * "DateTime mapping is not very intuitive while it is a common type."
 *
 * The strict-object check rejects scalar -> object conversion in general,
 * but JSON dates are conventionally transmitted as ISO 8601 strings.
 * The fix adds a narrow exemption for DateTime/DateTimeImmutable (and
 * subclasses) that ONLY applies when the value is a strict-shape ISO
 * date string. Loose forms PHP's DateTime constructor accepts ("now",
 * "+1month", "yesterday", "2pm") are intentionally still rejected --
 * those would defeat the purpose of strict mode.
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class DateTimeStrictTypes_Test extends \PHPUnit\Framework\TestCase
{
    private function strictMapper(): JsonMapper
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = true;
        return $jm;
    }

    /**
     * The original regression: an ISO 8601 date string must be accepted
     * for a DateTime property under strict mode.
     */
    public function testIsoDateTimeAcceptedUnderStrictMode(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"created":"2024-01-15T12:30:45+00:00"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTime::class, $r->created);
        $this->assertSame('2024-01-15T12:30:45+00:00', $r->created->format('c'));
    }

    /**
     * RFC 3339 with fractional seconds and Z offset must be accepted.
     */
    public function testRfc3339WithFractionalSecondsAccepted(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"modified":"2024-01-15T12:30:45.123Z"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTimeImmutable::class, $r->modified);
        $this->assertSame('2024-01-15', $r->modified->format('Y-m-d'));
    }

    /**
     * Date-only "YYYY-MM-DD" form must be accepted (common JSON shape).
     */
    public function testDateOnlyFormatAccepted(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"modified":"2024-01-15"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTimeImmutable::class, $r->modified);
        $this->assertSame('2024-01-15', $r->modified->format('Y-m-d'));
    }

    /**
     * "Space" separator instead of "T" must be accepted (a common
     * MySQL/Postgres serialisation that survives many JSON pipelines).
     */
    public function testSpaceSeparatorAccepted(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"created":"2024-01-15 12:30:45"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTime::class, $r->created);
        $this->assertSame('12:30:45', $r->created->format('H:i:s'));
    }

    /**
     * Compact +HHMM offset (no colon) is part of ISO 8601 and must be
     * accepted -- some Java/Joda producers emit this form.
     */
    public function testIsoOffsetWithoutColonAccepted(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"created":"2024-01-15T12:30:45+0100"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTime::class, $r->created);
        $this->assertSame(3600, $r->created->getOffset());
    }

    /**
     * Relative strings ("now", "+1month", "yesterday") must STILL be
     * rejected by strict mode -- accepting them would defeat the
     * security intent of the flag (caller-supplied magic strings).
     */
    public function testRelativeStringsStillRejected(): void
    {
        $cases = ['now', '+1month', 'yesterday', 'tomorrow 5pm', 'next monday'];
        foreach ($cases as $bad) {
            $threw = false;
            try {
                $this->strictMapper()->map(
                    json_decode(json_encode(['created' => $bad])),
                    new JsonMapperTest_DateTimes()
                );
            } catch (JsonMapper_Exception $e) {
                $threw = true;
                $this->assertStringContainsString(
                    'must be an object',
                    $e->getMessage(),
                    "expected strict-mode error for relative string \"$bad\""
                );
            }
            $this->assertTrue($threw, "relative string \"$bad\" should have been rejected");
        }
    }

    /**
     * Garbage that doesn't even look like a date must be rejected.
     */
    public function testGarbageStringsRejected(): void
    {
        $cases = ['hello', '2024', '2024/01/15', 'abc-de-fg'];
        foreach ($cases as $bad) {
            $threw = false;
            try {
                $this->strictMapper()->map(
                    json_decode(json_encode(['created' => $bad])),
                    new JsonMapperTest_DateTimes()
                );
            } catch (JsonMapper_Exception $e) {
                $threw = true;
            }
            $this->assertTrue($threw, "garbage string \"$bad\" should have been rejected");
        }
    }

    /**
     * Syntactically date-shaped but semantically impossible inputs
     * ("2024-13-40", "2024-02-30") must also be rejected -- the
     * round-trip check inside isSafeDateTimeMapping() catches these
     * via DateTime::getLastErrors() warnings.
     */
    public function testSemanticallyInvalidDatesRejected(): void
    {
        $cases = ['2024-13-40', '2024-02-30', '2024-00-15'];
        foreach ($cases as $bad) {
            $threw = false;
            try {
                $this->strictMapper()->map(
                    json_decode(json_encode(['created' => $bad])),
                    new JsonMapperTest_DateTimes()
                );
            } catch (JsonMapper_Exception $e) {
                $threw = true;
            }
            $this->assertTrue($threw, "invalid date \"$bad\" should have been rejected");
        }
    }

    /**
     * Nullable DateTime accepts JSON null without going through the
     * strict-object code path.
     */
    public function testNullableDateTimeAcceptsNull(): void
    {
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode('{"nullable":null}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertNull($r->nullable);
    }

    /**
     * Array of DateTimeImmutable: each scalar element must be converted
     * to an instance under strict mode (exercises the second fix site
     * in mapArray()).
     */
    public function testArrayOfDateTimeImmutableUnderStrictMode(): void
    {
        $json = '{"events":["2024-01-15T00:00:00Z","2024-02-20T00:00:00Z","2024-03-25T00:00:00Z"]}';
        /** @var JsonMapperTest_DateTimes $r */
        $r = $this->strictMapper()->map(
            json_decode($json),
            new JsonMapperTest_DateTimes()
        );
        $this->assertCount(3, $r->events);
        foreach ($r->events as $e) {
            $this->assertInstanceOf(\DateTimeImmutable::class, $e);
        }
        $this->assertSame('2024-02-20', $r->events[1]->format('Y-m-d'));
    }

    /**
     * Array element that is a relative string must still be rejected
     * even when the array's declared element type is DateTimeImmutable.
     */
    public function testArrayElementRelativeStringStillRejected(): void
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('must be an object');
        $this->strictMapper()->map(
            json_decode('{"events":["now"]}'),
            new JsonMapperTest_DateTimes()
        );
    }

    /**
     * Strict mode must still reject non-DateTime, non-enum object
     * properties given a scalar -- the exemption is intentionally
     * narrow.
     */
    public function testStrictCheckingStillRejectsArbitraryClassFromScalar(): void
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "pValueObject" must be an object, string given');

        $this->strictMapper()->map(
            json_decode('{"pValueObject":"2024-01-15"}'),
            new JsonMapperTest_Object()
        );
    }

    /**
     * Sanity check that with strict mode disabled the existing
     * (constructor-passthrough) behaviour is unchanged.
     */
    public function testDateTimeStillWorksWithStrictModeDisabled(): void
    {
        $jm = new JsonMapper();
        $jm->bStrictObjectTypeChecking = false;
        /** @var JsonMapperTest_DateTimes $r */
        $r = $jm->map(
            json_decode('{"created":"2024-01-15T12:30:45Z"}'),
            new JsonMapperTest_DateTimes()
        );
        $this->assertInstanceOf(\DateTime::class, $r->created);
    }
}
