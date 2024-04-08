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

/**
 * Unit tests for JsonMapper's simple type handling
 *
 * @category Tools
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper
 */
class SimpleTest extends \PHPUnit\Framework\TestCase
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
        $this->assertSame('stringvalue', $sn->str);
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
        $this->assertSame(1.2, $sn->fl);
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
        $this->assertSame(true, $sn->pbool);
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
        $this->assertSame(false, $sn->pboolean);
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
        $this->assertSame(123, $sn->pint);
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
        $this->assertSame(12345, $sn->pinteger);
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
        $this->assertSame(12345, $sn->mixed);

        $sn = $jm->map(
            json_decode('{"mixed":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame('12345', $sn->mixed);
    }

    /**
     * Test for "@var int|null" with int value
     */
    public function testMapSimpleNullableIntWithInt()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableInt":0}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame(0, $sn->pnullableInt);
    }

    /**
     * Test for "@var int|null" with null value
     */
    public function testMapSimpleNullableIntWithNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableInt":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->pnullableInt);
    }

    /**
     * Test for "@var int|null" with string value (force cast)
     */
    public function testMapSimpleNullableIntWithWrongType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableInt":"12345"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame(12345, $sn->pnullableInt);
    }

    /**
     * Test for "@var ?string" with string value
     */
    public function testMapSimpleNullableStringWithString()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableString":"test"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame('test', $sn->pnullableString);
    }

    /**
     * Test for "@var ?string" with null value
     */
    public function testMapSimpleNullableStringWithNull()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableString":null}'),
            new JsonMapperTest_Simple()
        );
        $this->assertNull($sn->pnullableString);
        $this->assertEquals(null, $sn->pnullableString);
    }

    /**
     * Test for "@var ?string" with int value (force cast)
     */
    public function testMapSimpleNullableStringWithWrongType()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"pnullableString":0}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame('0', $sn->pnullableString);
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
        $this->assertIsObject($sn->notype);
        $this->assertSame(['k' => 'v'], (array) $sn->notype);
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
        $this->assertSame('f', $sn->under_score);
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
        $this->assertSame(
            'blubb', $sn->internalData['under_score_setter']
        );
    }

    /**
     * Variable with hyphen (-)
     */
    public function testMapSimpleHyphen()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"hyphen-value":"test"}'),
            new JsonMapperTest_Simple()
        );

        $this->assertSame('test', $sn->hyphenValue);

    }

    /**
     * Variable with hyphen and a setter method
     */
    public function testMapSimpleHyphenSetter()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"hyphen-value-setter":"blubb"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame(
            'blubb', $sn->internalData['hyphen-value-setter']
        );

    }

    /**
     * Variable has no docblock, and has different caSiNg than object property
     */
    public function testMapCaseMismatchNoDocblock()
    {
        $jm = new JsonMapper();
        $sn = $jm->map(
            json_decode('{"noDocBlock":"blubb"}'),
            new JsonMapperTest_Simple()
        );
        $this->assertSame('blubb', $sn->nodocblock);
    }
}
?>
