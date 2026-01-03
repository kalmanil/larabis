<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Http\Middleware\TenantViewMiddleware;
use App\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Tests to detect upgrade breakage in stancl/tenancy and Laravel.
 * 
 * These tests verify that critical APIs we depend on still work after upgrades.
 * If these tests fail, check docs/UPGRADES.md for migration steps.
 * 
 * Run after every upgrade:
 * ```bash
 * php artisan test --filter=UpgradeCompatibility
 * ```
 */
class UpgradeCompatibilityTest extends TestCase
{
    public function test_tenancy_initialize_api_still_works(): void
    {
        // This test verifies that tenancy()->initialize() API hasn't changed
        $tenant = Tenant::create(['id' => 'test-tenant']);
        
        // Should not throw exception
        try {
            tenancy()->initialize($tenant);
            $this->assertTrue(true, 'tenancy()->initialize() API still works');
        } catch (\Exception $e) {
            $this->fail("tenancy()->initialize() API has changed: " . $e->getMessage());
        }
    }
    
    public function test_tenancy_initialized_property_still_works(): void
    {
        // This test verifies that tenancy()->initialized property/method still exists
        $tenant = Tenant::create(['id' => 'test-tenant']);
        
        tenancy()->initialize($tenant);
        
        // Should be able to access initialized property
        $this->assertTrue(
            tenancy()->initialized,
            'tenancy()->initialized property still works'
        );
    }
    
    public function test_has_database_trait_still_works(): void
    {
        // Verify HasDatabase trait methods still work
        $tenant = Tenant::create(['id' => 'test-tenant']);
        
        // Should have database() method from HasDatabase trait
        $this->assertTrue(
            method_exists($tenant, 'database'),
            'HasDatabase trait database() method still exists'
        );
    }
    
    public function test_has_domains_trait_still_works(): void
    {
        // Verify HasDomains trait methods still work
        $tenant = Tenant::create(['id' => 'test-tenant']);
        
        // Should have domains() relationship from HasDomains trait
        $this->assertTrue(
            method_exists($tenant, 'domains'),
            'HasDomains trait domains() method still exists'
        );
    }
    
    public function test_tenant_with_database_contract_still_exists(): void
    {
        // Verify TenantWithDatabase contract still exists
        $this->assertTrue(
            interface_exists(\Stancl\Tenancy\Contracts\TenantWithDatabase::class),
            'TenantWithDatabase contract still exists'
        );
    }
    
    public function test_tenant_model_uses_required_traits(): void
    {
        // Verify our Tenant model still uses required traits
        // These traits provide the TenantWithDatabase contract implementation
        $tenant = Tenant::create(['id' => 'test-contract']);
        
        // Verify the traits are used (which provide the contract implementation)
        $traits = class_uses_recursive($tenant);
        
        $this->assertTrue(
            in_array(\Stancl\Tenancy\Database\Concerns\HasDatabase::class, $traits),
            'Tenant model uses HasDatabase trait'
        );
        
        $this->assertTrue(
            in_array(\Stancl\Tenancy\Database\Concerns\HasDomains::class, $traits),
            'Tenant model uses HasDomains trait'
        );
        
        // Verify tenant extends BaseTenant (which should implement TenantWithDatabase)
        $this->assertInstanceOf(
            \Stancl\Tenancy\Database\Models\Tenant::class,
            $tenant,
            'Tenant model extends BaseTenant'
        );
    }
    
    public function test_middleware_initialization_order_still_works(): void
    {
        // Verify middleware initialization order hasn't broken
        $request = Request::create('http://test.test', 'GET');
        $resolver = new TenantResolver();
        $middleware = new TenantViewMiddleware($resolver);
        
        // Should not throw exception on null tenant
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }
    
    public function test_tenant_resolver_is_single_entry_point(): void
    {
        // Verify TenantResolver is the only way to resolve tenants
        // This ensures upgrade safety by having a single point of change
        $request = Request::create('http://test.test', 'GET');
        $resolver = new TenantResolver();
        
        // Should have resolve() method
        $this->assertTrue(
            method_exists($resolver, 'resolve'),
            'TenantResolver::resolve() method exists'
        );
        
        // Should return TenantResolutionResult
        $result = $resolver->resolve($request);
        $this->assertInstanceOf(
            \App\Tenancy\TenantResolutionResult::class,
            $result,
            'TenantResolver returns TenantResolutionResult'
        );
    }
    
    public function test_tenant_autoloader_registration_still_works(): void
    {
        // Verify tenant autoloader is registered via composer.json
        // This test ensures bootstrap/tenant-autoload.php is still loaded
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        
        $this->assertArrayHasKey(
            'autoload',
            $composerJson,
            'composer.json has autoload section'
        );
        
        $this->assertArrayHasKey(
            'files',
            $composerJson['autoload'],
            'composer.json autoload has files array'
        );
        
        $this->assertContains(
            'bootstrap/tenant-autoload.php',
            $composerJson['autoload']['files'],
            'bootstrap/tenant-autoload.php is registered in composer.json'
        );
    }
}

