<?php

use namespacetest\UnitData;

class NamespaceTest extends \PHPUnit\Framework\TestCase
{
    public function testMapArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"data":[{"value":"1.2"}]}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\Unit', $res->data[0]);
    }

    public function testMapSimpleArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"units":[{"value":"1.2"}]}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\Unit', $res->units[0]);
    }

    public function testMapSimpleStringArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"messages":["message 1", "message 2"]}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertNotNull($res->messages);
        $this->assertCount(2, $res->messages);
    }

    public function testMapMixed()
    {
        $mapper = new \JsonMapper();
        $json = '{"mixed":true}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertTrue($res->mixed);
    }

    public function testMapChildClassNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"user":{"name": "John Smith"}}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    public function testMapChildClassConstructorNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"user":"John Smith"}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    public function testMapChildObjectArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"data":[],"user":{"name": "John Smith"}}';
        /* @var \namespacetest\UnitData $res */
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\\ArrayObject', $res->data);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    public function testMapEmpty()
    {
        $this->expectException(\JsonMapper_Exception::class);
        $this->expectExceptionMessage('Empty type at property "namespacetest\UnitData::$empty"');
        $mapper = new \JsonMapper();
        $json = '{"empty":{}}';
        /* @var \namespacetest\UnitData $res */
        $res = $mapper->map(json_decode($json), new UnitData());
    }

    public function testMapCustomArrayObjectWithChildType()
    {
        $mapper = new \JsonMapper();
        $json = '{"users":[{"user":"John Smith"}]}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\model\UserList', $res->users);
        $this->assertInstanceOf('\namespacetest\model\User', $res->users[0]);
    }

    public function testMapCustomArrayObject()
    {
        $mapper = new \JsonMapper();
        $json = '{"aodata":["foo"]}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf('\namespacetest\model\MyArrayObject', $res->aodata);
        $this->assertIsString($res->aodata[0]);
        $this->assertEquals('foo', $res->aodata[0]);
    }

    /**
     * Test a setter method with a namespaced type hint that
     * is within another namespace than the object itself.
     */
    public function testSetterNamespacedTypeHint()
    {
        $mapper = new \JsonMapper();
        $json = '{"namespacedTypeHint":"Foo"}';
        $res = $mapper->map(json_decode($json), new UnitData());
        $this->assertInstanceOf('\namespacetest\UnitData', $res);
        $this->assertInstanceOf(
            '\othernamespace\Foo', $res->internalData['namespacedTypeHint']
        );
        $this->assertEquals(
            'Foo', $res->internalData['namespacedTypeHint']->name
        );
    }
}
?>
