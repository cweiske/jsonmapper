<?php
/**
 * Memory-optimized JsonMapper implementation
 *
 * This class extends JsonMapper to provide memory optimization strategies
 * for handling large ancestor chains in deeply nested JSON structures.
 *
 * @package JsonMapper
 * @author  GitHub Copilot
 * @license OSL-3.0 http://opensource.org/licenses/osl-3.0.php
 * @link    https://github.com/cweiske/jsonmapper
 */
class MemoryOptimizedJsonMapper extends JsonMapper
{
    /**
     * Maximum depth of ancestor tracking to prevent memory bloat
     * Set to 0 to disable depth limiting
     * 
     * @var int
     */
    public $maxAncestorDepth = 5;
    
    /**
     * Specific fields to preserve in ancestor objects
     * Empty array means preserve all fields (default behavior)
     * 
     * @var array
     */
    public $ancestorFields = [];
    
    /**
     * Whether to enable memory optimization
     * Set to false to use standard behavior
     * 
     * @var bool
     */
    public $enableOptimization = true;
    
    /**
     * Map JSON data with memory-optimized ancestor tracking
     *
     * @param object $json     JSON object to map from
     * @param object $object   Object to map into  
     * @param array  $ancestors Array of ancestor JSON objects
     *
     * @return object Mapped object
     */
    protected function mapWithAncestors($json, $object, $ancestors)
    {
        if ($this->enableOptimization) {
            $ancestors = $this->optimizeAncestors($ancestors);
        }
        
        // Use the parent's implementation but with optimized ancestors
        return parent::mapWithAncestors($json, $object, $ancestors);
    }
    
    /**
     * Apply memory optimizations to ancestor array
     *
     * @param array $ancestors Original ancestor array
     *
     * @return array Optimized ancestor array
     */
    protected function optimizeAncestors($ancestors)
    {
        // Skip optimization if disabled
        if (!$this->enableOptimization) {
            return $ancestors;
        }
        
        // Apply depth limiting if configured
        if ($this->maxAncestorDepth > 0 && count($ancestors) > $this->maxAncestorDepth) {
            $ancestors = array_slice($ancestors, -$this->maxAncestorDepth);
        }
        
        // Apply field filtering if configured
        if (!empty($this->ancestorFields)) {
            $ancestors = array_map([$this, 'filterAncestorFields'], $ancestors);
        }
        
        return $ancestors;
    }
    
    /**
     * Filter ancestor object to only include specified fields
     *
     * @param object $ancestor Original ancestor object
     *
     * @return object Filtered ancestor object
     */
    protected function filterAncestorFields($ancestor)
    {
        $filtered = new stdClass();
        
        foreach ($this->ancestorFields as $field) {
            if (isset($ancestor->$field)) {
                $filtered->$field = $ancestor->$field;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get memory usage statistics for ancestor tracking
     *
     * @return array Memory usage information
     */
    public function getMemoryStats()
    {
        return [
            'max_ancestor_depth' => $this->maxAncestorDepth,
            'filtered_fields' => $this->ancestorFields,
            'optimization_enabled' => $this->enableOptimization,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
}
