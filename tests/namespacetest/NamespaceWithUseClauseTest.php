<?php
namespace namespacetest;
use JsonMapper_Exception;

require_once __DIR__ . '/Unit.php';
require_once __DIR__ . '/UnitDataWithUseClause.php';
require_once __DIR__ . '/model/MyArrayObject.php';
require_once __DIR__ . '/model/User.php';
require_once __DIR__ . '/model/UserList.php';
require_once __DIR__ . '/../othernamespace/Foo.php';
require_once __DIR__ . '/../Foo2.php';
/**
 * Class NamespaceWithUseClauseTest
 *
 * @package namespacetest
 */
class NamespaceWithUseClauseTest extends \PHPUnit_Framework_TestCase
{
    public function testMapArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"data":[{"value":"1.2"}]}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\Unit', $res->data[0]);
    }

    public function testMapSimpleArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"units":[{"value":"1.2"}]}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\Unit', $res->units[0]);
    }


    public function testMapClassMatrixNamespace()
    {
        $mapper = new \JsonMapper();
        $json =
            '{"unit_matrix":[[{"value":"1.2"}, {"value":"2.2"}], [{"value":"3.2"}, {"value":"4.2"}]]}';
        /** @var UnitDataWithUseClause $res */
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\Unit', $res->unit_matrix[1][1]);
    }

    public function testMapIntMatrixNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"int_matrix":[[1, 2], [3, 4]]}';
        /** @var UnitDataWithUseClause $res */
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertEquals(3, $res->int_matrix[1][0]);
    }

    public function testMultidimensionalArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json =
            '{"multidimensional_array":[[[[{"name": "John Smith"},{"name": "Scarlet Johansson"}]]], []]}';
        /** @var UnitDataWithUseClause $res */
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\model\User',
            $res->multidimensional_array[0][0][0][0]);
        $this->assertEquals('Scarlet Johansson',
            $res->multidimensional_array[0][0][0][1]->name);
    }

    public function testMapClassWithNoNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"foo2":{"name": "John Smith"}}';
        /** @var UnitDataWithUseClause $res */
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('Foo2', $res->foo2);
        $this->assertEquals('John Smith', $res->foo2->name);
    }

    public function testMapSimpleStringArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"messages":["message 1", "message 2"]}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertNotNull($res->messages);
        $this->assertCount(2, $res->messages);
    }

    public function testMapChildClassNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"user":{"name": "John Smith"}}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    public function testMapChildClassConstructorNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"user":"John Smith"}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    public function testMapChildObjectArrayNamespace()
    {
        $mapper = new \JsonMapper();
        $json = '{"data":[],"user":{"name": "John Smith"}}';
        /* @var \namespacetest\UnitDataWithUseClause $res */
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\\ArrayObject', $res->data);
        $this->assertInstanceOf('\namespacetest\model\User', $res->user);
    }

    /**
     * @expectedException JsonMapper_Exception
     * @expectedExceptionMessage Empty type at property "namespacetest\UnitDataWithUseClause::$empty"
     */
    public function testMapEmpty()
    {
        $mapper = new \JsonMapper();
        $json = '{"empty":{}}';
        /* @var \namespacetest\UnitDataWithUseClause $res */
        $mapper->map(json_decode($json), new UnitDataWithUseClause());
    }

    public function testMapCustomArrayObjectWithChildType()
    {
        $mapper = new \JsonMapper();
        $json = '{"users":[{"user":"John Smith"}]}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\model\UserList', $res->users);
        $this->assertInstanceOf('\namespacetest\model\User', $res->users[0]);
    }

    public function testMapCustomArrayObject()
    {
        $mapper = new \JsonMapper();
        $json = '{"aodata":["foo"]}';
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf('\namespacetest\model\MyArrayObject', $res->aodata);
        $this->assertInternalType('string', $res->aodata[0]);
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
        $res = $mapper->map(json_decode($json), new UnitDataWithUseClause());
        $this->assertInstanceOf('\namespacetest\UnitDataWithUseClause', $res);
        $this->assertInstanceOf(
            '\othernamespace\Foo', $res->internalData['namespacedTypeHint']
        );
        $this->assertEquals(
            'Foo', $res->internalData['namespacedTypeHint']->name
        );
    }
}
?>
