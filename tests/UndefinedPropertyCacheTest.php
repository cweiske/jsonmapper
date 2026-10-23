<?php
/**
 * Unit tests for JsonMapper option "bCacheUndefinedProperties".
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class UndefinedPropertyCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testMissesAreCachedByDefault()
    {
        $jm = new UndefinedPropertyCacheTest_Mapper();
        $jm->map(json_decode('{"undefined":1}'), new JsonMapperTest_Simple());

        $cache = $jm->getInspectedClasses();
        $this->assertArrayHasKey(JsonMapperTest_Simple::class, $cache);
        $this->assertArrayHasKey(
            'undefined',
            $cache[JsonMapperTest_Simple::class]
        );
        $this->assertFalse(
            $cache[JsonMapperTest_Simple::class]['undefined'][0]
        );
    }

    public function testMissesAreNotCachedWhenDisabled()
    {
        $jm = new UndefinedPropertyCacheTest_Mapper();
        $jm->bCacheUndefinedProperties = false;
        $obj = $jm->map(
            json_decode('{"pbool":true,"undefined":1,"alsoUndefined":2}'),
            new JsonMapperTest_Simple()
        );

        //mapping itself is unaffected
        $this->assertTrue($obj->pbool);

        $cache = $jm->getInspectedClasses();
        $this->assertSame(
            array('pbool'),
            array_keys($cache[JsonMapperTest_Simple::class] ?? array())
        );
    }

    public function testMapArrayDoesNotCacheMissesWhenDisabled()
    {
        $jm = new UndefinedPropertyCacheTest_Mapper();
        $jm->bCacheUndefinedProperties = false;
        $objects = $jm->mapArray(
            json_decode(
                '[{"pbool":true,"undefined":1},{"pbool":false,"undefined":2}]'
            ),
            array(),
            JsonMapperTest_Simple::class
        );

        $this->assertTrue($objects[0]->pbool);
        $this->assertFalse($objects[1]->pbool);

        $cache = $jm->getInspectedClasses();
        $this->assertSame(
            array('pbool'),
            array_keys($cache[JsonMapperTest_Simple::class] ?? array())
        );
    }
}

/**
 * JsonMapper subclass that exposes the inspection result cache.
 *
 * @category Tests
 * @package  JsonMapper
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class UndefinedPropertyCacheTest_Mapper extends JsonMapper
{
    public function getInspectedClasses()
    {
        return $this->arInspectedClasses;
    }
}
?>
