<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
require_once 'JsonMapperTest/Broken.php';
require_once 'JsonMapperTest/DependencyInjector.php';
require_once 'JsonMapperTest/Simple.php';
require_once 'JsonMapperTest/Logger.php';
require_once 'JsonMapperTest/PrivateWithSetter.php';
require_once 'JsonMapperTest/ValueObject.php';

/**
 * Unit tests for JsonMapper that don't fit in other categories
 *
 * @category Netresearch
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     http://cweiske.de/
 */
class OtherTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage JsonMapper::map() requires first argument to be an object, NULL given.
     */
    public function testMapNullJson()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(null, new JsonMapperTest_Simple());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage JsonMapper::map() requires second argument to be an object, NULL given.
     */
    public function testMapNullObject()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(new stdClass(), null);
    }

    /**
     * Test for "@var "
     *
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage Empty type at property "JsonMapperTest_Simple::$empty"
     */
    public function testMapEmpty()
    {
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

        $this->assertInternalType('object', $sn->internalData['typehint']);
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['typehint']
        );
        $this->assertEquals(
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
        $this->assertInternalType('object', $sn->internalData['docblock']);
        $this->assertInstanceOf(
            'JsonMapperTest_Simple', $sn->internalData['docblock']
        );
        $this->assertEquals(
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
        $this->assertInternalType('object', $sn->internalData['notype']);
        $this->assertInstanceOf(
            'stdClass', $sn->internalData['notype']
        );
        $this->assertEquals(
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
        $this->assertInternalType('null', $sn->getProtectedStrNoSetter());
        $this->assertEquals(
            array(
                array(
                    'info',
                    'Property {property} has no public setter method in {class}',
                    array(
                        'class' => 'JsonMapperTest_Simple',
                        'property' => 'protectedStrNoSetter'
                    )
                )
            ),
            $logger->log
        );
    }

    /**
     * @expectedException        JsonMapper_Exception
     * @expectedExceptionMessage Required property "pMissingData" of class JsonMapperTest_Broken is missing in JSON data
     */
    public function testMissingDataException()
    {
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

    /**
     * @expectedException        JsonMapper_Exception
     * @expectedExceptionMessage JSON property "undefinedProperty" does not exist in object of type JsonMapperTest_Broken
     */
    public function testUndefinedPropertyException()
    {
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

        $this->assertEquals(123, $sn->ADDundefinedProperty);
    }

    public function setUnknownProperty($object, $propName, $jsonValue)
    {
        $object->{'ADD' . $propName} = $jsonValue;
    }

    public function testPrivatePropertyWithPublicSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateProperty" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    /**
     * @expectedException        JsonMapper_Exception
     * @expectedExceptionMessage JSON property "privateNoSetter" has no public setter method in object of type PrivateWithSetter
     */
    public function testPrivatePropertyWithNoSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateProperty());
        $this->assertTrue(empty($logger->log));
    }

    public function testPrivatePropertyWithNoSetterButAllowed()
    {
        $jm = new JsonMapper();
        $jm->bIgnoreVisibility = true;

        $json   = '{"privateNoSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateNoSetter());
    }

    /**
     * @expectedException        JsonMapper_Exception
     * @expectedExceptionMessage JSON property "privatePropertyPrivateSetter" has no public setter method in object of type PrivateWithSetter
     */
    public function testPrivatePropertyWithPrivateSetter()
    {
        $jm = new JsonMapper();
        $jm->bExceptionOnUndefinedProperty = true;
        $logger = new JsonMapperTest_Logger();
        $jm->setLogger($logger);

        $json   = '{"privatePropertyPrivateSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());
    }

    public function testPrivateSetterButAllowed()
    {
        $jm = new JsonMapper();
        $jm->bIgnoreVisibility = true;
        $jm->bExceptionOnUndefinedProperty = true;

        $json   = '{"privateSetter" : 1}';
        $result = $jm->map(json_decode($json), new PrivateWithSetter());

        $this->assertEquals(1, $result->getPrivateSetter());
    }

    public function testSetterIsPreferredOverProperty()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"setterPreferredOverProperty":"foo"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->setterPreferredOverProperty);
        $this->assertEquals(
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

        $this->assertEquals('first level', $sn->str);
        $this->assertEquals('database', $sn->db);

        $this->assertEquals('second level', $sn->simple->str);
        $this->assertEquals('database', $sn->simple->db);
    }
}
?>
