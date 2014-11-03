<?php
namespace namespacetest;
require_once __DIR__ . '/Unit.php';
require_once __DIR__ . '/UnitData.php';
require_once __DIR__ . '/model/User.php';

class NamespaceTest extends \PHPUnit_Framework_TestCase
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
}
?>
