<?php
/**
 * Support class for ancestor testing - main contract class 
 */
class Contract {
    /** @var string */
    public $version;
    
    /** @var string */
    public $type;
    
    /** @var string */
    public $action;
    
    /** @var ContractBody */
    public $body;
}
