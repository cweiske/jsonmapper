<?php
/**
 * Memory usage analysis for JsonMapper ancestors
 * 
 * This script demonstrates the memory impact of ancestor tracking
 * and shows how optimization can help.
 */

require_once 'vendor/autoload.php';

echo "=== JsonMapper Ancestor Memory Analysis ===\n\n";

// Test 1: Memory usage without ancestors (baseline)
echo "1. Baseline memory usage (no ancestors):\n";
$jm = new JsonMapper();

$json = createLargeNestedJson(5, 50); // 5 levels deep, 50 chars per field
$target = new stdClass();

$memBefore = memory_get_usage(true);
$result = $jm->map($json, $target);
$memAfter = memory_get_usage(true);

echo "   Memory used: " . formatBytes($memAfter - $memBefore) . "\n\n";

// Test 2: Memory usage with ancestor tracking
echo "2. Memory usage with ancestor tracking:\n";
$jm = new JsonMapper();
$memoryUsages = [];

$jm->classMap['DeepClass'] = function($class, $jvalue, $ancestors) use (&$memoryUsages) {
    $memoryUsages[] = memory_get_usage(true);
    return 'stdClass';
};

// Create target with typed property to trigger classMap
$target = new class {
    /** @var DeepClass */
    public $level1;
};

$json = createLargeNestedJsonWithClasses(5, 100);

$memBefore = memory_get_usage(true);
$result = $jm->map($json, $target);  
$memAfter = memory_get_usage(true);

echo "   Memory used: " . formatBytes($memAfter - $memBefore) . "\n";
echo "   Peak memory during classMap calls: " . formatBytes(max($memoryUsages) - $memBefore) . "\n\n";

// Test 3: Memory optimization strategies
echo "3. Memory optimization strategies:\n\n";

echo "   Strategy A: Depth Limiting\n";
$ancestors = createMockAncestors(10, 200); // 10 deep, 200 chars each
$memBefore = memory_get_usage(true);
$limited = limitAncestorDepth($ancestors, 3);
$memAfter = memory_get_usage(true);
echo "   Original: " . count($ancestors) . " ancestors, " . formatBytes(calculateAncestorMemory($ancestors)) . "\n";
echo "   Limited: " . count($limited) . " ancestors, " . formatBytes(calculateAncestorMemory($limited)) . "\n";
echo "   Memory saved: " . formatBytes(calculateAncestorMemory($ancestors) - calculateAncestorMemory($limited)) . "\n\n";

echo "   Strategy B: Field Filtering\n";
$ancestors = createMockAncestors(5, 500); // 5 levels, 500 chars of data each
$filtered = filterAncestorFields($ancestors, ['id', 'version', 'type']);
echo "   Original: " . formatBytes(calculateAncestorMemory($ancestors)) . "\n";
echo "   Filtered: " . formatBytes(calculateAncestorMemory($filtered)) . "\n";
echo "   Memory saved: " . formatBytes(calculateAncestorMemory($ancestors) - calculateAncestorMemory($filtered)) . "\n\n";

echo "   Strategy C: Combined Optimization\n";
$ancestors = createMockAncestors(15, 1000); // 15 deep, 1KB each
$optimized = filterAncestorFields(limitAncestorDepth($ancestors, 5), ['id', 'version']);
echo "   Original: " . count($ancestors) . " ancestors, " . formatBytes(calculateAncestorMemory($ancestors)) . "\n";
echo "   Optimized: " . count($optimized) . " ancestors, " . formatBytes(calculateAncestorMemory($optimized)) . "\n"; 
echo "   Memory saved: " . formatBytes(calculateAncestorMemory($ancestors) - calculateAncestorMemory($optimized)) . "\n";
echo "   Reduction: " . round((1 - calculateAncestorMemory($optimized) / calculateAncestorMemory($ancestors)) * 100, 1) . "%\n\n";

// Summary
echo "=== Summary ===\n";
echo "For deeply nested JSON structures, ancestor tracking can use significant memory.\n";
echo "Optimization strategies can reduce memory usage by 80-90% in extreme cases.\n";
echo "Most real-world JSON has < 10 levels, making the impact manageable.\n";
echo "Use MemoryOptimizedJsonMapper for memory-constrained environments.\n";

function createLargeNestedJson($depth, $stringSize) {
    $data = str_repeat('x', $stringSize);
    $json = new stdClass();
    $json->data = $data;
    $json->extra1 = $data;
    $json->extra2 = $data;
    
    $current = $json;
    for ($i = 1; $i < $depth; $i++) {
        $current->nested = new stdClass();
        $current->nested->data = $data;
        $current->nested->extra1 = $data;
        $current->nested->extra2 = $data;
        $current = $current->nested;
    }
    
    return $json;
}

function createLargeNestedJsonWithClasses($depth, $stringSize) {
    $data = str_repeat('x', $stringSize);
    $json = new stdClass();
    $json->data = $data;
    $json->extra1 = $data;
    $json->extra2 = $data;
    
    $current = $json;
    for ($i = 1; $i < $depth; $i++) {
        $current->level1 = new stdClass();
        $current->level1->data = $data;
        $current->level1->extra1 = $data;
        $current->level1->extra2 = $data;
        $current = $current->level1;
    }
    
    return $json;
}

function createMockAncestors($count, $dataSize) {
    $ancestors = [];
    $data = str_repeat('x', $dataSize);
    
    for ($i = 0; $i < $count; $i++) {
        $ancestor = new stdClass();
        $ancestor->id = "id_$i";
        $ancestor->version = "v$i";
        $ancestor->type = "type_$i";
        $ancestor->large_data = $data;
        $ancestor->extra_field_1 = $data;
        $ancestor->extra_field_2 = $data;
        $ancestors[] = $ancestor;
    }
    
    return $ancestors;
}

function limitAncestorDepth($ancestors, $maxDepth) {
    if (count($ancestors) <= $maxDepth) {
        return $ancestors;
    }
    return array_slice($ancestors, -$maxDepth);
}

function filterAncestorFields($ancestors, $allowedFields) {
    return array_map(function($ancestor) use ($allowedFields) {
        $filtered = new stdClass();
        foreach ($allowedFields as $field) {
            if (isset($ancestor->$field)) {
                $filtered->$field = $ancestor->$field;
            }
        }
        return $filtered;
    }, $ancestors);
}

function calculateAncestorMemory($ancestors) {
    return strlen(serialize($ancestors));
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, $precision) . ' ' . $units[$unit];
}
