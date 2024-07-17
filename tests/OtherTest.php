<?php
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

/**
 * Unit tests for JsonMapper that don't fit in other categories
 *
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class OtherTest extends \PHPUnit\Framework\TestCase
{
    public function testMapNullJson()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::map() requires first argument to be an object, NULL given.');
        $jm = new JsonMapper();
        $sn = $jm->map(null, new JsonMapperTest_Simple());
    }

    public function testMapNullObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonMapper::map() requires second argument to be an object or existing class name, NULL given.');
        $jm = new JsonMapper();
        $sn = $jm->map(new stdClass(), null);
    }

    /**
     * Test for "@var "
     */
    public function testMapEmpty()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('Empty type at property "JsonMapperTest_Simple::$empty"');
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode(
                '{"empty":{"a":"b"}}'
            ),
            new JsonMapperTest_Simple()
        );
    }

    /**
     * There is no property, but a setter method.
     * The parameter has a type hint.
     */
    public function testMapOnlySetterTypeHint()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyTypeHint":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );

        $this->assertIsObject($sn->internalData['typehint']);
        $this->assertInstanceOf(
            JsonMapperTest_Simple::class, $sn->internalData['typehint']
        );
        $this->assertSame(
            'stringvalue', $sn->internalData['typehint']->str
        );
    }

    /**
     * There is no property, but a setter method.
     * It indicates the type in the docblock's @param annotation
     */
    public function testMapOnlySetterDocblock()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyDocblock":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertIsObject($sn->internalData['docblock']);
        $this->assertInstanceOf(
            JsonMapperTest_Simple::class, $sn->internalData['docblock']
        );
        $this->assertSame(
            'stringvalue', $sn->internalData['docblock']->str
        );
    }

    /**
     * There is no property, but a setter method, but it indicates no type
     */
    public function testMapOnlySetterNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"simpleSetterOnlyNoType":{"str":"stringvalue"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertIsObject($sn->internalData['notype']);
        $this->assertInstanceOf(
            stdClass::class, $sn->internalData['notype']
        );
        $this->assertSame(
            'stringvalue', $sn->internalData['notype']->str
        );
    }

    /**
     * Test for protected properties that have no setter method
     */
    public function testMapProtectedWithoutSetterMethod()
    {
        $jm = new JsonMapper();
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);
        $sn = $jm->map(
            json_decode('{"protectedStrNoSetter":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->getProtectedStrNoSetter());
        $this->assertSame(
            array(
                array(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array(
                        'property' => 'protectedStrNoSetter',
                        'class' => 'JsonMapperTest_Simple',
                    )
                )
            ),
            $logger->log
        );
    }

    public function testMissingDataException()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('Required property "pMissingData" of class JsonMapperTest_Broken is missing in JSON data');
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{}'),
            new JsonMapperTest_Broken()
        );
    }

    /**
     * We check that checkMissingData exits cleanly; needed for 100% coverage.
     */
    public function testMissingDataNoException()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnMissingData = true;
        $sn = $jm->map(
            json_decode('{"pMissingData":1}'),
            new JsonMapperTest_Broken()
        );
        $this->assertTrue(true);
    }

    public function testUndefinedPropertyException()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "undefinedProperty" does not exist in object of type JsonMapperTest_Broken');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $sn = $jm->map(
            json_decode('{"undefinedProperty":123}'),
            new JsonMapperTest_Broken()
        );
    }

    public function testUndefinedPropertyHandler()
    {
        $jm = new JsonMapper();
        $jm->undefinedPropertyHandler = array($this, 'setUnknownProperty');
        $sn = $jm->map(
            json_decode('{"undefinedProperty":123}'),
            new JsonMapperTest_Broken()
        );

        $this->assertSame(123, $sn->store['undefinedProperty']);
    }

    public function setUnknownProperty($object, $propName, $jsonValue)
    {
        $object->store[$propName] = $jsonValue;
    }

    public function testPrivatePropertyWithPublicSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateProperty" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetter());

        $this->assertSame(1, $result->getPrivateProperty());
        $this->assertEmpty($logger->log);
    }

    public function testPrivatePropertyWithNoSetter()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "privateNoSetter" has no public setter method in object of type JsonMapperTest_PrivateWithSetter');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetter());
    }

    public function testPrivatePropertyWithNoSetterButAllowed()
    {
        $jm = new JsonMapper();
        $jm->bIgnoreVisibility = true;

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetter());

        $this->assertSame(1, $result->getPrivateNoSetter());
    }

    public function testPrivatePropertyInParentClassWithNoSetterButAllowed()
    {
        $jm = new JsonMapper();
        $jm->bIgnoreVisibility = true;

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetterSub());

        $this->assertSame(1, $result->getPrivateNoSetter());
    }

    public function testPrivatePropertyWithPrivateSetter()
    {
        $this->expectException(JsonMapper_Exception::class);
        $this->expectExceptionMessage('JSON property "privatePropertyPrivateSetter" has no public setter method in object of type JsonMapperTest_PrivateWithSetter');
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privatePropertyPrivateSetter" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetter());
    }

    public function testPrivatePropertySetterWithoutDoc()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;

        $result = $jm->map(json_decode('{"privatePropertySetterWithoutDoc" : 1}'), new JsonMapperTest_PrivateWithSetter());
        $this->assertSame(1, $result->getPrivatePropertySetterWithoutDoc());
    }

    public function testPrivatePropertyNullableNotNullSetterWithoutDoc()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;

        $result = $jm->map(json_decode('{"privatePropertyNullableSetterWithoutDoc" : 1}'), new JsonMapperTest_PrivateWithSetter());
        $this->assertSame(1, $result->getPrivatePropertyNullableSetterWithoutDoc());
    }

    public function testPrivatePropertyNullableNullSetterWithoutDoc()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;

        $result = $jm->map(json_decode('{"privatePropertyNullableSetterWithoutDoc" : null}'), new JsonMapperTest_PrivateWithSetter());
        $this->assertNull($result->getPrivatePropertyNullableSetterWithoutDoc());
    }

    public function testPrivateArrayOfSimple()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;

        $result = $jm->map(
            json_decode(
                '{"privateArrayOfSimple" : [{"pbool": true, "pint": 42}, {"pbool": false, "pint": 24}]}'
            ),
            new JsonMapperTest_PrivateWithSetter()
        );

        $a = new JsonMapperTest_Simple;
        $a->pbool = true;
        $a->pint = 42;

        $b = new JsonMapperTest_Simple;
        $b->pbool = false;
        $b->pint = 24;

        $this->assertEquals(
            [$a, $b],
            $result->getPrivateArrayOfSimple()
        );
    }

    public function testPrivateSetterButAllowed()
    {
        $jm = new JsonMapper();
        $jm->bIgnoreVisibility = true;
        $jm->bExceptionOnUndefinedProperty = true;

        $json   = '{"privateSetter" : 1}';
        $result = $jm->map(json_decode($json), new JsonMapperTest_PrivateWithSetter());

        $this->assertSame(1, $result->getPrivateSetter());
    }

    public function testSetterIsPreferredOverProperty()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"setterPreferredOverProperty":"foo"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame(
            'set via setter: foo', $sn->setterPreferredOverProperty
        );
    }

    public function testCaseInsensitivePropertyMatching()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            (object) array('PINT' => 2),
            new JsonMapperTest_Simple()
        );

        $this->assertSame(2, $sn->pint);
    }

    public function testDependencyInjection()
    {
        $jm = new JsonMapperTest_DependencyInjector();

        $sn = $jm->map(
            (object) array(
                'str' => 'first level',
                'simple' => (object) array(
                    'str' => 'second level'
                )
            ),
            $jm->createInstance('JsonMapperTest_Simple')
        );

        $this->assertSame('first level', $sn->str);
        $this->assertSame('database', $sn->db);

        $this->assertSame('second level', $sn->simple->str);
        $this->assertSame('database', $sn->simple->db);
    }
}
?>
