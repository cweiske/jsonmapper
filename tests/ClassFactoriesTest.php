<?php
/**
 * Unit tests for JsonMapper's classFactories
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ClassFactoriesTest extends \PHPUnit\Framework\TestCase
{
    public function createDateTime($jvalue): \DateTime
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jvalue)) {
            return new \DateTime($jvalue);
        }
        throw new \Exception('Invalid date pattern');
    }

    public function testClassFactoriesWithoutLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classFactories['DateTime'] = [$this, 'createDateTime'];
        $sn = $jm->map(
            json_decode('{"datetime": "2026-06-05"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInstanceOf(\DateTime::class, $sn->datetime);
        $this->assertSame(
            '2026-06-05',
            $sn->datetime->format('Y-m-d')
        );
    }

    public function testClassFactoriesWithLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->classFactories['\\DateTime'] = [$this, 'createDateTime'];
        $sn = $jm->map(
            json_decode('{"datetime": "2026-06-05"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInstanceOf(\DateTime::class, $sn->datetime);
        $this->assertSame(
            '2026-06-05',
            $sn->datetime->format('Y-m-d')
        );
    }

    public function testClassFactoriesError()
    {
        $jm = new JsonMapper();
        $jm->classFactories[\DateTime::class] = [$this, 'createDateTime'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid date pattern');

        $jm->map(
            json_decode('{"datetime": "05/06/2026"}'),
            new JsonMapperTest_Object()
        );
    }

    public function testClassFactoriesWithAnonymousFunction()
    {
        $jm = new JsonMapper();
        $jm->classFactories[\DateTime::class] = function ($jvalue) {
            return new \DateTime($jvalue);
        };
        $sn = $jm->map(
            json_decode('{"datetime": "2026-06-05"}'),
            new JsonMapperTest_Object()
        );

        $this->assertInstanceOf(\DateTime::class, $sn->datetime);
        $this->assertSame(
            '2026-06-05',
            $sn->datetime->format('Y-m-d')
        );
    }
}
?>
