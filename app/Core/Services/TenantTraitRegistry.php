<?php

namespace App\Core\Services;

use App\Tenancy\TenantContext;
use App\Helpers\TenancyHelper;

/**
 * Tenant Trait Registry
 *
 * Dynamically resolves and calls tenant-specific trait methods without requiring
 * changes to shared controller code when adding new tenants.
 *
 * Prefer passing TenantContext (injected) for explicit dependencies; falls back to
 * container when null for backward compatibility.
 *
 * Trait Discovery Priority:
 * 1. Tenant-View-Specific: App\Features\Pages\Tenants\{tenant_id}\Views\{view_code}\Traits\PageLogic
 * 2. Tenant-Specific: App\Features\Pages\Tenants\{tenant_id}\Traits\PageLogic
 * 3. View-Specific: App\Features\Pages\Views\{view_code}\Traits\PageLogic
 * 4. Base: App\Shared\Traits\Base\PageLogic
 *
 * @see docs/UPGRADES.md for upgrade safety information
 */
class TenantTraitRegistry
{
    /**
     * Get data from tenant-specific trait method
     *
     * @param string $methodName Method name to call (e.g., 'getPageData')
     * @param object $caller The calling object (usually $this from controller)
     * @param array $args Additional arguments to pass to the method
     * @param TenantContext|null $context Injected tenant context, or null to resolve from container
     * @return array|null Returns array data or null if no trait found
     */
    public static function getData(string $methodName, object $caller, array $args = [], ?TenantContext $context = null): ?array
    {
        $context = $context ?? (app()->bound(TenantContext::class) ? app(TenantContext::class) : null);
        $tenant = $context ? $context->getTenant() : TenancyHelper::currentTenant();
        $view = $context ? $context->getView() : TenancyHelper::currentView();

        if (!$tenant) {
            return null;
        }

        $tenantId = $tenant->id;
        
        // Priority 1: Tenant-View-Specific (most specific)
        if ($view && $view->code) {
            $traitClass = "App\\Features\\Pages\\Tenants\\{$tenantId}\\Views\\{$view->code}\\Traits\\PageLogic";
            $result = self::callIfExists($traitClass, $methodName, $caller, $args);
            if ($result !== null) {
                return $result;
            }
        }
        
        // Priority 2: Tenant-Specific
        $traitClass = "App\\Features\\Pages\\Tenants\\{$tenantId}\\Traits\\PageLogic";
        $result = self::callIfExists($traitClass, $methodName, $caller, $args);
        if ($result !== null) {
            return $result;
        }
        
        // Priority 3: View-Specific (fallback to view-level trait)
        if ($view && $view->code) {
            $traitClass = "App\\Features\\Pages\\Views\\{$view->code}\\Traits\\PageLogic";
            $result = self::callIfExists($traitClass, $methodName, $caller, $args);
            if ($result !== null) {
                return $result;
            }
        }
        
        // Priority 4: Base (fallback to base trait)
        $traitClass = "App\\Shared\\Traits\\Base\\PageLogic";
        return self::callIfExists($traitClass, $methodName, $caller, $args);
    }
    
    /**
     * Call a method on a trait class if it exists
     * 
     * @param string $className Fully qualified class name
     * @param string $methodName Method name to call
     * @param object $caller The calling object
     * @param array $args Additional arguments
     * @return array|null Returns array data or null if class/method doesn't exist
     */
    protected static function callIfExists(
        string $className,
        string $methodName,
        object $caller,
        array $args
    ): ?array {
        // Check if class/trait exists
        if (!class_exists($className) && !trait_exists($className)) {
            return null;
        }
        
        // Check if method exists on the class/trait
        if (!method_exists($className, $methodName)) {
            return null;
        }
        
        try {
            $reflection = new \ReflectionClass($className);
            $method = $reflection->getMethod($methodName);
            
            // Check if method is static
            if ($method->isStatic()) {
                // Static method - call directly
                $result = $className::$methodName($caller, ...$args);
                if (is_array($result)) {
                    return $result;
                }
            } else {
                // Instance method - check if it exists on caller (trait may be used by caller)
                if (method_exists($caller, $methodName)) {
                    $result = $caller->$methodName(...$args);
                    if (is_array($result)) {
                        return $result;
                    }
                }
                
                // If trait is not used by caller, try to instantiate a temporary object
                // (only works for regular classes, not traits)
                if (!$reflection->isTrait()) {
                    $instance = new $className();
                    if (method_exists($instance, $methodName)) {
                        $result = $instance->$methodName(...$args);
                        if (is_array($result)) {
                            return $result;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::debug("Tenant trait call failed", [
                'class' => $className,
                'method' => $methodName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        return null;
    }
    
    /**
     * Check if a tenant-specific trait exists
     *
     * @param string|null $methodName Optional method name to check
     * @param TenantContext|null $context Injected tenant context, or null to resolve from container
     * @return bool True if trait exists (and method exists if specified)
     */
    public static function hasTrait(?string $methodName = null, ?TenantContext $context = null): bool
    {
        $context = $context ?? (app()->bound(TenantContext::class) ? app(TenantContext::class) : null);
        $tenant = $context ? $context->getTenant() : TenancyHelper::currentTenant();
        $view = $context ? $context->getView() : TenancyHelper::currentView();

        if (!$tenant) {
            return false;
        }

        $tenantId = $tenant->id;
        
        // Check tenant-view-specific
        if ($view && $view->code) {
            $traitClass = "App\\Features\\Pages\\Tenants\\{$tenantId}\\Views\\{$view->code}\\Traits\\PageLogic";
            if (class_exists($traitClass) || trait_exists($traitClass)) {
                if ($methodName === null || method_exists($traitClass, $methodName)) {
                    return true;
                }
            }
        }
        
        // Check tenant-specific
        $traitClass = "App\\Features\\Pages\\Tenants\\{$tenantId}\\Traits\\PageLogic";
        if (class_exists($traitClass) || trait_exists($traitClass)) {
            if ($methodName === null || method_exists($traitClass, $methodName)) {
                return true;
            }
        }
        
        return false;
    }
}
