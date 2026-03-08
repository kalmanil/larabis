<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProvisionTenants extends Command
{
    protected $signature = 'tenants:provision
                            {--tenant=* : Specific tenant ID(s) to provision (defaults to all)}
                            {--skip-migrate : Skip running tenant migrations after DB creation}';

    protected $description = 'Create missing tenant databases and run their migrations. Safe to re-run — skips already-existing databases.';

    public function handle(): int
    {
        $tenantIds = $this->option('tenant');

        $tenants = $tenantIds
            ? Tenant::whereIn('id', $tenantIds)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found.');
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $failed  = 0;

        foreach ($tenants as $tenant) {
            $dbName = $tenant->database()->getName();

            try {
                if ($tenant->database()->manager()->databaseExists($dbName)) {
                    $this->line("  <fg=yellow>skip</>  {$tenant->id} → {$dbName} (already exists)");
                    $skipped++;
                    continue;
                }

                $tenant->database()->manager()->createDatabase($tenant);
                $this->line("  <fg=green>created</>  {$tenant->id} → {$dbName}");
                $created++;
            } catch (\Throwable $e) {
                $this->line("  <fg=red>failed</>   {$tenant->id} → {$dbName}: {$e->getMessage()}");
                $failed++;
                continue;
            }

            if (! $this->option('skip-migrate')) {
                $this->line("    <fg=cyan>migrating</> {$tenant->id}…");
                Artisan::call('tenants:migrate', [
                    '--tenants' => [$tenant->id],
                    '--force'   => true,
                ], $this->output);
            }
        }

        $this->newLine();
        $this->info("Done. Created: {$created} | Skipped: {$skipped} | Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
