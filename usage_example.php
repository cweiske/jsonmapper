<?php
/**
 * Practical example demonstrating MemoryOptimizedJsonMapper usage
 *
 * This example shows how to use memory optimization for deeply nested
 * JSON structures with ancestor-dependent class mapping.
 */

require_once 'vendor/autoload.php';
require_once 'src/MemoryOptimizedJsonMapper.php';

// Example classes for version-dependent contract mapping
class ContractV1 {
    public $title;
    public $body;
}

class ContractV2 {
    public $title;
    public $description;
    public $metadata;
}

class LegacyContract {
    public $name; // Different field structure
    public $content;
}

class ContractCollection {
    /** @var Contract[] */
    public $contracts;
}

echo "=== MemoryOptimizedJsonMapper Usage Example ===\n\n";

// Example 1: Basic usage with ancestor-based mapping
echo "1. Version-dependent contract mapping:\n";

$jm = new MemoryOptimizedJsonMapper();
$jm->maxAncestorDepth = 3; // Limit ancestor tracking to 3 levels
$jm->ancestorFields = ['version', 'format', 'id']; // Only preserve key fields

$jm->classMap['Contract'] = function($class, $jvalue, $ancestors) {
    // Use ancestor information to determine the correct contract class
    foreach ($ancestors as $ancestor) {
        if (isset($ancestor->version)) {
            if ($ancestor->version === '1.0' && isset($ancestor->format) && $ancestor->format === 'legacy') {
                return 'LegacyContract';
            } elseif ($ancestor->version === '2.0') {
                return 'ContractV2';
            } elseif ($ancestor->version === '1.0') {
                return 'ContractV1';
            }
        }
    }
    return 'ContractV1'; // Default fallback
};

$contractJson = json_decode('{
    "version": "2.0",
    "format": "standard",
    "id": "collection_123",
    "large_metadata": "' . str_repeat('x', 500) . '",
    "processing_data": "' . str_repeat('y', 300) . '",
    "temp_fields": "' . str_repeat('z', 200) . '",
    "contracts": [
        {
            "title": "Contract A",
            "description": "Modern contract with metadata",
            "metadata": {"priority": "high"}
        },
        {
            "title": "Contract B", 
            "description": "Another v2 contract",
            "metadata": {"priority": "low"}
        }
    ]
}');

$collection = $jm->map($contractJson, new ContractCollection());

echo "   Mapped " . count($collection->contracts) . " contracts\n";
echo "   Contract types: ";
foreach ($collection->contracts as $i => $contract) {
    echo get_class($contract);
    if ($i < count($collection->contracts) - 1) echo ", ";
}
echo "\n\n";

// Example 2: Memory usage comparison
echo "2. Memory usage comparison:\n";

// Test with large nested structure
$largeJson = createLargeNestedContractJson(5, 300); // 5 levels, 300 chars per field

// Standard JsonMapper
$standardMapper = new JsonMapper();
$standardMapper->classMap['Contract'] = function($class, $jvalue, $ancestors) {
    return 'ContractV1'; // Simple mapping
};

$memBefore = memory_get_usage(true);
$standardResult = $standardMapper->map($largeJson, new ContractCollection());
$standardMemory = memory_get_usage(true) - $memBefore;

// Optimized JsonMapper
$optimizedMapper = new MemoryOptimizedJsonMapper();
$optimizedMapper->maxAncestorDepth = 2;
$optimizedMapper->ancestorFields = ['version', 'id'];
$optimizedMapper->classMap['Contract'] = function($class, $jvalue, $ancestors) {
    return 'ContractV1'; // Simple mapping
};

$memBefore = memory_get_usage(true);
$optimizedResult = $optimizedMapper->map($largeJson, new ContractCollection());
$optimizedMemory = memory_get_usage(true) - $memBefore;

echo "   Standard memory usage: " . formatBytes($standardMemory) . "\n";
echo "   Optimized memory usage: " . formatBytes($optimizedMemory) . "\n";
if ($standardMemory > 0) {
    $savings = (1 - $optimizedMemory / $standardMemory) * 100;
    echo "   Memory savings: " . round($savings, 1) . "%\n";
}
echo "\n";

// Example 3: Configuration options
echo "3. Configuration options:\n";

$jm = new MemoryOptimizedJsonMapper();
echo "   Default maxAncestorDepth: " . $jm->maxAncestorDepth . "\n";
echo "   Default ancestorFields: " . (empty($jm->ancestorFields) ? 'none (all fields preserved)' : implode(', ', $jm->ancestorFields)) . "\n";
echo "   Default optimization enabled: " . ($jm->enableOptimization ? 'yes' : 'no') . "\n\n";

$jm->maxAncestorDepth = 3;
$jm->ancestorFields = ['version', 'type', 'id'];
$jm->enableOptimization = true;

echo "   Configured maxAncestorDepth: " . $jm->maxAncestorDepth . "\n";
echo "   Configured ancestorFields: " . implode(', ', $jm->ancestorFields) . "\n";
echo "   Optimization enabled: " . ($jm->enableOptimization ? 'yes' : 'no') . "\n\n";

$stats = $jm->getMemoryStats();
echo "   Memory stats:\n";
foreach ($stats as $key => $value) {
    echo "     $key: " . (is_bool($value) ? ($value ? 'true' : 'false') : 
                          (is_array($value) ? '[' . implode(', ', $value) . ']' : 
                           (is_numeric($value) && $value > 1000000 ? formatBytes($value) : $value))) . "\n";
}

echo "\n=== Usage Tips ===\n";
echo "- Use maxAncestorDepth to limit memory usage with deep nesting\n";
echo "- Use ancestorFields to preserve only the data you need\n";
echo "- Set enableOptimization=false to disable all optimizations\n"; 
echo "- Memory savings are most significant with deep nesting (5+ levels)\n";
echo "- Most real-world JSON has <10 levels, making optimization less critical\n";
echo "- Monitor memory usage with getMemoryStats() method\n";

function createLargeNestedContractJson($depth, $dataSize) {
    $largeData = str_repeat('x', $dataSize);
    
    $json = new stdClass();
    $json->version = '1.0';
    $json->id = 'root';
    $json->large_field_1 = $largeData;
    $json->large_field_2 = $largeData;
    $json->large_field_3 = $largeData;
    
    $current = $json;
    for ($i = 1; $i < $depth; $i++) {
        $current->contracts = [new stdClass()];
        $current->contracts[0]->version = "1.$i";
        $current->contracts[0]->id = "level_$i";
        $current->contracts[0]->large_field_1 = $largeData;
        $current->contracts[0]->large_field_2 = $largeData;
        $current->contracts[0]->large_field_3 = $largeData;
        $current = $current->contracts[0];
    }
    
    return $json;
}

function formatBytes($size, $precision = 2) {
    if ($size == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, $precision) . ' ' . $units[$unit];
}
