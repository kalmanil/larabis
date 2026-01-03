<?php

namespace Tests\Feature;

use App\Helpers\TenancyHelper;
use Tests\TestCase;

class TenantViewFallbackTest extends TestCase
{
    public function test_helper_returns_null_when_no_tenant_context(): void
    {
        // Ensure no tenant context is set (RefreshDatabase resets app, but be explicit)
        if (app()->bound(\App\Contracts\CurrentTenant::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenant::class);
        }
        if (app()->bound(\App\Contracts\CurrentTenantView::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenantView::class);
        }
        // Also clear string keys for backward compatibility fallback
        if (app()->bound('currentTenant')) {
            app()->forgetInstance('currentTenant');
        }
        if (app()->bound('currentTenantView')) {
            app()->forgetInstance('currentTenantView');
        }
        
        // No tenant context set
        $this->assertNull(TenancyHelper::currentTenant());
        $this->assertNull(TenancyHelper::currentView());
        $this->assertFalse(TenancyHelper::isTenantContext());
    }
    
    public function test_view_path_fallback_when_no_context(): void
    {
        // Ensure no tenant context is set
        if (app()->bound(\App\Contracts\CurrentTenant::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenant::class);
        }
        if (app()->bound(\App\Contracts\CurrentTenantView::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenantView::class);
        }
        // Also clear string keys for backward compatibility fallback
        if (app()->bound('currentTenant')) {
            app()->forgetInstance('currentTenant');
        }
        if (app()->bound('currentTenantView')) {
            app()->forgetInstance('currentTenantView');
        }
        
        // When no tenant/view context, should return view name as-is
        $viewPath = TenancyHelper::getViewPath('home');
        $this->assertEquals('home', $viewPath);
    }
    
    public function test_view_helper_throws_when_no_context(): void
    {
        // Ensure no tenant context is set
        if (app()->bound(\App\Contracts\CurrentTenant::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenant::class);
        }
        if (app()->bound(\App\Contracts\CurrentTenantView::class)) {
            app()->forgetInstance(\App\Contracts\CurrentTenantView::class);
        }
        // Also clear string keys for backward compatibility fallback
        if (app()->bound('currentTenant')) {
            app()->forgetInstance('currentTenant');
        }
        if (app()->bound('currentTenantView')) {
            app()->forgetInstance('currentTenantView');
        }
        
        // TenancyHelper::view() should throw when context is missing
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tenant or view context not available');
        
        TenancyHelper::view('home', []);
    }
}

