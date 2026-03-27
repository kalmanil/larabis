<?php

namespace Tests\Unit;

use App\Tenancy\TenantViewPathConfigurator;
use Tests\TestCase;

class TenantViewPathConfiguratorTest extends TestCase
{
    public function test_prepends_tenant_resources_views_when_directory_exists(): void
    {
        $tenantId = 'flashcards';
        $tenantViews = base_path("tenants/{$tenantId}/resources/views");

        if (! is_dir($tenantViews)) {
            $this->markTestSkipped('flashcards tenant views directory not present');
        }

        TenantViewPathConfigurator::prependTenantResourcesViews($tenantId);

        $paths = config('view.paths');
        $this->assertNotEmpty($paths);
        $this->assertSame($tenantViews, $paths[0]);
        $this->assertContains(resource_path('views'), $paths);
    }

    public function test_noop_for_empty_tenant_id(): void
    {
        $before = config('view.paths');
        TenantViewPathConfigurator::prependTenantResourcesViews(null);
        TenantViewPathConfigurator::prependTenantResourcesViews('');
        $this->assertSame($before, config('view.paths'));
    }

    public function test_noop_when_tenant_views_directory_missing(): void
    {
        $before = config('view.paths');
        TenantViewPathConfigurator::prependTenantResourcesViews('nonexistent-tenant-xyz');
        $this->assertSame($before, config('view.paths'));
    }
}
