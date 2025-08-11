<?php
/**
 * Support class for ancestor testing - base contract body
 */
class ContractBody {
    public $version;
    public $body_data;
    
    public static function determineClass($class, $json, $ancestors) {
        // Look for the contract object in the ancestors
        foreach ($ancestors as $ancestor) {
            if (is_object($ancestor)) {
                // Check if this ancestor has the properties we need  
                if (isset($ancestor->version) && isset($ancestor->type)) {
                    if ($ancestor->version <= 2) {
                        switch(strtoupper($ancestor->type)) {
                            case "VOTE":
                                if ($ancestor->version == 1) {
                                    return 'ContractVoteLegacy';
                                }
                                if ($ancestor->version == 2) {
                                    return 'ContractVote';
                                }
                                break;
                        }
                    }
                }
                
                // Check if this ancestor is an object containing other objects
                if (is_object($ancestor)) {
                    foreach (get_object_vars($ancestor) as $prop) {
                        if (is_object($prop) && isset($prop->version) && isset($prop->type)) {
                            if ($prop->version <= 2) {
                                switch(strtoupper($prop->type)) {
                                    case "VOTE":
                                        if ($prop->version == 1) {
                                            return 'ContractVoteLegacy';
                                        }
                                        if ($prop->version == 2) {
                                            return 'ContractVote';
                                        }
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Default fallback
        return 'ContractBody';
    }
}
