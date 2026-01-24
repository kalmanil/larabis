<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TenantManageView extends Command
{
    protected $signature = 'tenant:view {tenant_id}
                            {domain : Domain for the view}
                            {--name= : View name (required for add, optional for update)}
                            {--code= : View code (defaults to name)}
                            {--update : Update existing view (renames/merges folders when code changes)}
                            {--switch : Switch view mapping only (no folder changes)}';

    protected $description = 'Add or update a view for a tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $domain = $this->argument('domain');
        $isUpdate = $this->option('update');
        
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant '{$tenantId}' not found!");
            return 1;
        }

        if ($isUpdate) {
            return $this->updateView($domain);
        } else {
            return $this->addView($tenant, $domain);
        }
    }

    protected function addView(Tenant $tenant, string $domain): int
    {
        // Check if view already exists
        if (TenantView::where('domain', $domain)->exists()) {
            $this->error("View with domain '{$domain}' already exists! Use --update to modify it.");
            return 1;
        }

        $name = $this->option('name') ?: $this->inferNameFromDomain($domain);
        $code = $this->option('code') ?: $name;

        // Register domain for stancl tenancy
        $tenant->domains()->firstOrCreate(['domain' => $domain]);

        // Create view
        $view = TenantView::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'domain' => $domain,
            'code' => $code,
        ]);

        // Create view folder structure
        $this->createViewFolders($tenant->id, $code);

        $this->info("✓ View '{$name}' ({$code}) added to tenant '{$tenant->id}' for domain: {$domain}");
        return 0;
    }

    protected function updateView(string $domain): int
    {
        $view = TenantView::where('domain', $domain)->first();
        if (!$view) {
            $this->error("View with domain '{$domain}' not found!");
            return 1;
        }

        $name = $this->option('name');
        $code = $this->option('code');
        $isSwitch = $this->option('switch');
        $oldCode = $view->code;
        $tenantId = $view->tenant_id;

        if (!$name && !$code) {
            $this->error("Please provide --name and/or --code option");
            return 1;
        }

        if ($name) {
            $oldName = $view->name;
            $view->name = $name;
            $this->info("  Updated name: {$oldName} → {$name}");
        }

        if ($code && $code !== $oldCode) {
            if ($isSwitch) {
                $this->warn("  Switch-only: skipping folder rename for {$oldCode} → {$code}");
            } else {
                // Rename the view folder if code changed
                $this->renameViewFolder($tenantId, $oldCode, $code);
            }
            $view->code = $code;
            $this->info("  Updated code: {$oldCode} → {$code}");
        }

        $view->save();
        
        // Ensure view folders exist (in case code changed)
        if ($code && !$isSwitch) {
            $this->createViewFolders($tenantId, $code);
        }
        
        $this->info("✓ View updated for domain: {$domain}");
        return 0;
    }

    protected function renameViewFolder(string $tenantId, string $oldCode, string $newCode): void
    {
        // Use simplified consolidated structure: tenants/{tenant_id}/resources/views/{code}/
        $oldPath = base_path("tenants/{$tenantId}/resources/views/{$oldCode}");
        $newPath = base_path("tenants/{$tenantId}/resources/views/{$newCode}");

        if (File::exists($oldPath) && !File::exists($newPath)) {
            File::move($oldPath, $newPath);
            $this->info("  Renamed folder: {$oldCode} → {$newCode}");
        } elseif (File::exists($oldPath) && File::exists($newPath)) {
            $this->warn("  Warning: Both folders exist. Merging contents...");
            // Move contents from old to new if new exists
            $files = File::allFiles($oldPath);
            foreach ($files as $file) {
                $relativePath = $file->getRelativePathname();
                $newFile = "{$newPath}/{$relativePath}";
                if (!File::exists($newFile)) {
                    File::ensureDirectoryExists(dirname($newFile));
                    File::move($file->getPathname(), $newFile);
                }
            }
            // Remove old folder after merging
            File::deleteDirectory($oldPath);
            $this->info("  Merged and removed old folder: {$oldCode}");
        }
    }

    protected function inferNameFromDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        return count($parts) >= 3 ? $parts[0] : 'default';
    }

    protected function createViewFolders(string $tenantId, string $code): void
    {
        // Use simplified consolidated structure: tenants/{tenant_id}/resources/views/{code}/
        $tenantBasePath = base_path("tenants/{$tenantId}");
        $viewsBasePath = "{$tenantBasePath}/resources/views";
        $viewPath = "{$viewsBasePath}/{$code}";

        // Create tenant base directory structure if it doesn't exist
        if (!File::exists($tenantBasePath)) {
            File::makeDirectory($tenantBasePath, 0755, true);
            $this->line("    Created folder: tenants/{$tenantId}/");
        }

        // Create views directory if it doesn't exist
        if (!File::exists($viewsBasePath)) {
            File::makeDirectory($viewsBasePath, 0755, true);
            $this->line("    Created folder: tenants/{$tenantId}/resources/views/");
        }

        // Create view code folder if it doesn't exist
        if (!File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
            $this->line("    Created folder: tenants/{$tenantId}/resources/views/{$code}/");
        }

        // Create a basic home.blade.php if it doesn't exist
        $homeViewPath = "{$viewPath}/home.blade.php";
        if (!File::exists($homeViewPath)) {
            $homeViewContent = $this->generateHomeView($tenantId, $code);
            File::put($homeViewPath, $homeViewContent);
            $this->line("    Created starter view: tenants/{$tenantId}/resources/views/{$code}/home.blade.php");
        }
    }

    protected function generateHomeView(string $tenantId, string $code): string
    {
        $tenantIdEscaped = addslashes($tenantId);
        $codeEscaped = addslashes($code);
        
        return <<<BLADE
@extends('layouts.app')

@section('title', 'Home - ' . ucfirst('{$tenantIdEscaped}'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">Welcome to {{ ucfirst(\$tenant->id ?? '{$tenantIdEscaped}') }}</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">{{ ucfirst(\$view->name ?? '{$codeEscaped}') }} View</h2>
            <p class="text-gray-600 mb-4">
                This is the <strong>{{ \$view->name ?? '{$codeEscaped}' }}</strong> view for the <strong>{{ \$tenant->id ?? '{$tenantIdEscaped}' }}</strong> tenant.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Current Context:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>Tenant:</strong> {{ \$tenant->id ?? '{$tenantIdEscaped}' }}</li>
                    <li><strong>View:</strong> {{ \$view->name ?? '{$codeEscaped}' }}</li>
                    <li><strong>Code:</strong> {{ \$view->code ?? '{$codeEscaped}' }}</li>
                    <li><strong>Domain:</strong> {{ \$view->domain ?? request()->getHost() }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
BLADE;
    }
}

