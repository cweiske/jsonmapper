<?php

require_once 'JsonMapperTest/ParentClassMap.php';

class ParentClassMapTest extends \PHPUnit\Framework\TestCase {

    public function testParentClassMap()
    {
        $mapper = function($object, $child, $parent) {

            // Check parent type and return the correct class name
            if($parent->type == "protocol") {
                if($child->type == "protocol") {
                    return '\ProtocolClassBody_Test';
                }
            }
        };

        $jm = new JsonMapper();
        
        // Body requires a parent parameter to be checked before we can map it
        // Based on the 'type' property of the parent, we can determine the type of the child
        $jm->classMap[ParentClassBody_Test::class] = $mapper;

        $sn = $jm->map(
            json_decode('{"type":"protocol","body":{"type":"protocol","name":"protocol","version":"1.0","content":"test"}}'),
            new ParentClass_Test()
        );

        // Verify that the body property is mapped and that name is present
        $this->assertIsObject($sn->body);
        $this->assertArrayHasKey('name', get_object_vars($sn->body));

    }

}

 
?>