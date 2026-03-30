<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Stancl\Tenancy\Commands\Migrate as StanclMigrate;
use Stancl\Tenancy\Events\DatabaseMigrated;
use Stancl\Tenancy\Events\MigratingDatabase;

/**
 * Runs tenant migrations from:
 * 1) database/migrations/tenant (shared across tenants)
 * 2) tenants/{tenant_id}/database/migrations (tenant app / submodule)
 *
 * Respects explicit --path from CLI (skips auto-discovery for that run).
 */
class TenantsMigrateCommand extends StanclMigrate
{
    public function handle()
    {
        foreach (config('tenancy.migration_parameters') as $parameter => $value) {
            if ($parameter === '--path') {
                continue;
            }
            if (! $this->input->hasParameterOption($parameter)) {
                $this->input->setOption(ltrim($parameter, '-'), $value);
            }
        }

        if (! $this->confirmToProceed()) {
            return;
        }

        $userPaths = $this->input->getOption('path');

        tenancy()->runForMultiple($this->option('tenants'), function ($tenant) use ($userPaths) {
            $this->line("Tenant: {$tenant->getTenantKey()}");

            event(new MigratingDatabase($tenant));

            if (! empty($userPaths)) {
                parent::handle();
            } else {
                $paths = $this->migrationPathsForTenant($tenant);
                if ($paths === []) {
                    $this->warn('No migration directories found (expected database/migrations/tenant and/or tenants/'.$tenant->getTenantKey().'/database/migrations).');
                    event(new DatabaseMigrated($tenant));

                    return;
                }
                $this->input->setOption('path', $paths);
                $this->input->setOption('realpath', true);
                parent::handle();
            }

            event(new DatabaseMigrated($tenant));
        });
    }

    /**
     * @return list<string>
     */
    protected function migrationPathsForTenant(\Stancl\Tenancy\Contracts\Tenant $tenant): array
    {
        $paths = [];

        $shared = database_path('migrations/tenant');
        if (is_dir($shared)) {
            $paths[] = $shared;
        }

        $tenantSpecific = base_path('tenants/'.$tenant->getTenantKey().'/database/migrations');
        if (is_dir($tenantSpecific)) {
            $paths[] = $tenantSpecific;
        }

        return $paths;
    }
}
