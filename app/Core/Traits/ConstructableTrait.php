<?php

namespace App\Core\Traits;

trait ConstructableTrait
{
    protected $traitInitialized = [];
    
    public function __construct()
    {
        if (method_exists(parent::class, '__construct')) {
            parent::__construct();
        }
        
        $this->initializeAllTraits();
    }
    
    protected function initializeAllTraits()
    {
        $traits = class_uses_recursive(static::class);
        
        foreach ($traits as $trait) {
            $traitName = $this->getTraitName($trait);
            $constructor = 'traitConstruct' . $traitName;
            
            if (method_exists($this, $constructor) && !isset($this->traitInitialized[$traitName])) {
                $this->$constructor();
                $this->traitInitialized[$traitName] = true;
            }
        }
    }
    
    protected function getTraitName($trait)
    {
        $parts = explode('\\', $trait);
        return end($parts);
    }
}

