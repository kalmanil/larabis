<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantView;
use App\Helpers\TenancyHelper;
use Tests\TestCase;

class TenantViewTest extends TestCase
{
    public function test_tenant_view_override_works(): void
    {
        // Create tenant
        $tenant = Tenant::create(['id' => 'test']);
        
        // Create view
        $view = TenantView::create([
            'tenant_id' => $tenant->id,
            'name' => 'default',
            'domain' => 'test.example.com',
            'code' => 'default',
        ]);
        
        // Simulate tenant context using contracts
        $context = new \App\Tenancy\TenantContext($tenant, $view);
        app()->instance(\App\Contracts\CurrentTenant::class, $context);
        app()->instance(\App\Contracts\CurrentTenantView::class, $context);
        tenancy()->initialize($tenant);
        
        // Test view path resolution
        $viewPath = TenancyHelper::getViewPath('home');
        $this->assertEquals('tenants.test::default.home', $viewPath);
        
        // Verify helper methods work
        $this->assertNotNull(TenancyHelper::currentTenant());
        $this->assertNotNull(TenancyHelper::currentView());
        $this->assertTrue(TenancyHelper::isTenantContext());
        $this->assertTrue(TenancyHelper::isViewCode('default'));
    }
    
    public function test_admin_view_code_detection(): void
    {
        $tenant = Tenant::create(['id' => 'testadmin']);
        $view = TenantView::create([
            'tenant_id' => $tenant->id,
            'name' => 'admin',
            'domain' => 'admin.test.example.com',
            'code' => 'admin',
        ]);
        
        // Simulate tenant context using contracts
        $context = new \App\Tenancy\TenantContext($tenant, $view);
        app()->instance(\App\Contracts\CurrentTenant::class, $context);
        app()->instance(\App\Contracts\CurrentTenantView::class, $context);
        tenancy()->initialize($tenant);
        
        $this->assertTrue(TenancyHelper::isAdminView());
        $this->assertTrue(TenancyHelper::isViewCode('admin'));
        $this->assertFalse(TenancyHelper::isViewCode('default'));
    }
}

