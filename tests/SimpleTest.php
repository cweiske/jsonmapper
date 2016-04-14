<?php
/**
 * Part of JsonMapper
 *
 * PHP version 5
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
require_once 'JsonMapperTest/Simple.php';

/**
 * Unit tests for JsonMapper's simple type handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class SimpleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for "@var string"
     */
    public function testMapSimpleString()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"str":"stringvalue"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->str);
        $this->assertEquals('stringvalue', $sn->str);
    }

    /**
     * Test for "@var float"
     */
    public function testMapSimpleFloat()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"fl":"1.2"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('float', $sn->fl);
        $this->assertEquals(1.2, $sn->fl);
    }

    /**
     * Test for "@var bool"
     */
    public function testMapSimpleBool()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pbool":"1"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('boolean', $sn->pbool);
        $this->assertEquals(true, $sn->pbool);
    }

    /**
     * Test for "@var boolean"
     */
    public function testMapSimpleBoolean()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pboolean":"0"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('boolean', $sn->pboolean);
        $this->assertEquals(false, $sn->pboolean);
    }

    /**
     * Test for "@var int"
     */
    public function testMapSimpleInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pint":"123"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pint);
        $this->assertEquals(123, $sn->pint);
    }

    /**
     * Test for "@var integer"
     */
    public function testMapSimpleInteger()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pinteger":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pinteger);
        $this->assertEquals(12345, $sn->pinteger);
    }

    /**
     * Test for "@var mixed"
     */
    public function testMapSimpleMixed()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"mixed":12345}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->mixed);
        $this->assertEquals('12345', $sn->mixed);

        $sn = $jm->map(
            json_decode('{"mixed":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->mixed);
        $this->assertEquals(12345, $sn->mixed);
    }

    /**
     * Test for "@var int|null" with int value
     */
    public function testMapSimpleNullableInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":0}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pnullable);
        $this->assertEquals(0, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with null value
     */
    public function testMapSimpleNullableNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('null', $sn->pnullable);
        $this->assertEquals(null, $sn->pnullable);
    }

    /**
     * Test for "@var int|null" with string value
     */
    public function testMapSimpleNullableWrong()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullable":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('integer', $sn->pnullable);
        $this->assertEquals(12345, $sn->pnullable);
    }

    /**
     * Test for variable with no @var annotation
     */
    public function testMapSimpleNoType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"notype":{"k":"v"}}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('object', $sn->notype);
        $this->assertEquals((object) array('k' => 'v'), $sn->notype);
    }

    /**
     * Variable with an underscore
     */
    public function testMapSimpleUnderscore()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score":"f"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType('string', $sn->under_score);
        $this->assertEquals('f', $sn->under_score);
    }

    /**
     * Variable with an underscore and a setter method
     */
    public function testMapSimpleUnderscoreSetter()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"under_score_setter":"blubb"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertInternalType(
            'string', $sn->internalData['under_score_setter']
        );
        $this->assertEquals(
            'blubb', $sn->internalData['under_score_setter']
        );
    }
}
?>
