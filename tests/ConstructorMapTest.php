<?php
/**
 * Unit tests for JsonMapper's constructorMap
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class ConstructorMapTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorMapWithoutLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->constructorMap[\DateTime::class] = function ($jvalue) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jvalue)) {
                return new \DateTime($jvalue);
            } else {
                throw new \Exception('Invalid date pattern');
            }
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

    public function testConstructorMapWithLeadingBackslash()
    {
        $jm = new JsonMapper();
        $jm->constructorMap['\\DateTime'] = function ($jvalue) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jvalue)) {
                return new \DateTime($jvalue);
            } else {
                throw new \Exception('Invalid date pattern');
            }
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

    public function testConstructorMapError()
    {
        $jm = new JsonMapper();
        $jm->constructorMap[\DateTime::class] = function ($jvalue) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jvalue)) {
                return new \DateTime($jvalue);
            } else {
                throw new \Exception('Invalid date pattern');
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid date pattern');

        $jm->map(
            json_decode('{"datetime": "05/06/2026"}'),
            new JsonMapperTest_Object()
        );
    }

    public function testConstructorMapWithFunctionNameCallable()
    {
        $jm = new JsonMapper();
        $jm->constructorMap[\DateTime::class] = 'ConstructorMapTest_createDateTime';
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

    public function testConstructorMapWithObjectMethodCallable()
    {
        $jm = new JsonMapper();
        $jm->constructorMap[\DateTime::class] = [
            new class {
                public function createDateTime($jvalue)
                {
                    return new \DateTime($jvalue);
                }
            },
            'createDateTime'
        ];
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

    public function testConstructorMapWithClassName()
    {
        $jm = new JsonMapper();
        $jm->constructorMap[JsonMapperTest_Simple::class] = function () {
            $obj = new JsonMapperTest_Simple();
            $obj->str = 'initial';
            return $obj;
        };
        $sn = $jm->map(
            json_decode('{"pbool": true}'),
            JsonMapperTest_Simple::class
        );

        $this->assertSame(
            'initial',
            $sn->str
        );
    }
}

function ConstructorMapTest_createDateTime($jvalue)
{
    return new \DateTime($jvalue);
}
?>