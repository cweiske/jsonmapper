<?php
/**
 * Unit tests for JsonMapper's classMap with ancestor support (issue #212)
 *
 * @category Tests
 * @package  JsonMapper
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @license  OSL-3.0 http://opensource.org/licenses/osl-3.0
 * @link     https://github.com/cweiske/jsonmapper/issues/212
 */

require_once __DIR__ . '/support/ContractsContainer.php';
require_once __DIR__ . '/support/Contract.php';
require_once __DIR__ . '/support/ContractBody.php';
require_once __DIR__ . '/support/ContractVoteLegacy.php';
require_once __DIR__ . '/support/ContractVote.php';

class ClassMapAncestorsTest extends \PHPUnit\Framework\TestCase
{
    public function testClassMapWithAncestors()
    {
        $json = json_decode('
        {
            "contracts": {
                "0": {
                    "version": "2",
                    "type": "vote",
                    "action": "A",
                    "body": {
                        "version": "1",
                        "body_data": "some_data"
                    }
                }
            }
        }');

        $jm = new JsonMapper();
        $jm->classMap['ContractBody'] = function($class, $jvalue, $ancestors) {
            return ContractBody::determineClass($class, $jvalue, $ancestors);
        };

        $result = $jm->map($json, new ContractsContainer());

        // Check that we got the correct class based on parent data
        $this->assertInstanceOf('ContractVote', $result->contracts['0']->body);
        $this->assertEquals('some_data', $result->contracts['0']->body->body_data);
    }

    public function testClassMapWithAncestorsLegacy()
    {
        $json = json_decode('
        {
            "contracts": {
                "0": {
                    "version": "1",
                    "type": "vote", 
                    "action": "A",
                    "body": {
                        "version": "1",
                        "body_data": "legacy_data"
                    }
                }
            }
        }');

        $jm = new JsonMapper();
        $jm->classMap['ContractBody'] = function($class, $jvalue, $ancestors) {
            return ContractBody::determineClass($class, $jvalue, $ancestors);
        };

        $result = $jm->map($json, new ContractsContainer());

        // Check that we got the legacy class based on parent version
        $this->assertInstanceOf('ContractVoteLegacy', $result->contracts['0']->body);
        $this->assertEquals('legacy_data', $result->contracts['0']->body->body_data);
    }

    public function testClassMapWithDeepAncestors()
    {
        $json = json_decode('
        {
            "root_version": "3",
            "contracts": {
                "0": {
                    "version": "2",
                    "type": "vote",
                    "action": "A",
                    "body": {
                        "version": "1",
                        "body_data": "deep_data"
                    }
                }
            }
        }');

        $jm = new JsonMapper();
        $jm->classMap['ContractBody'] = function($class, $jvalue, $ancestors) {
            // Test that we can access the root level data in ancestors
            if (count($ancestors) >= 1) {
                // Look for root_version in any ancestor
                foreach ($ancestors as $ancestor) {
                    if (is_object($ancestor) && isset($ancestor->root_version)) {
                        if ($ancestor->root_version == "3") {
                            return 'ContractVote'; // Use newer version for root v3
                        }
                    }
                }
            }
            return ContractBody::determineClass($class, $jvalue, $ancestors);
        };

        $result = $jm->map($json, new ContractsContainer());

        // Check that we got the correct class based on root ancestor data  
        $this->assertInstanceOf('ContractVote', $result->contracts['0']->body);
        $this->assertEquals('deep_data', $result->contracts['0']->body->body_data);
    }

    public function testClassMapBackwardCompatibility()
    {
        // Test that classMap functions with 2 parameters still work
        $json = json_decode('{"contracts": {"0": {"version": "2", "type": "vote", "action": "A", "body": {"test": "data"}}}}');

        $jm = new JsonMapper();
        $jm->classMap['ContractBody'] = function($class, $jvalue) {
            // Old-style function without ancestors parameter
            return 'ContractVote';
        };

        $result = $jm->map($json, new ContractsContainer());

        $this->assertInstanceOf('ContractVote', $result->contracts['0']->body);
    }
}
