<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {id} 
                            {--domains=* : Domains to create views for (e.g., lapp.test, admin.lapp.test)}
                            {--domain= : Single domain (backward compatibility, creates view named "default")}';

    protected $description = 'Create a new tenant with one or more views';

    public function handle()
    {
        $id = $this->argument('id');
        
        // Create tenant
        $tenant = Tenant::create([
            'id' => $id,
        ]);

        $this->info("Tenant '{$id}' created.");

        // Collect domains to process
        $domainsToProcess = [];
        
        // Handle new --domains option (multiple views, all equal)
        $domains = $this->option('domains');
        if (!empty($domains)) {
            $domainsToProcess = $domains;
        }
        // Handle backward compatibility with single --domain
        elseif ($domain = $this->option('domain')) {
            $domainsToProcess = [$domain];
        }
        // Default: create single view named 'default'
        else {
            $domainsToProcess = ["{$id}.test"];
        }

        // Create views for each domain (all equal)
        foreach ($domainsToProcess as $domain) {
            $this->createViewForDomain($tenant, $domain);
        }

        // Run tenant migrations
        $this->call('tenants:migrate', ['--tenants' => $id]);

        $this->info("Tenant '{$id}' setup complete!");
    }

    protected function createViewForDomain(Tenant $tenant, string $domain): void
    {
        // Infer view name and code from domain
        // e.g., "admin.lapp.test" -> name="admin", code="admin"
        // e.g., "lapp.test" -> name="default", code="default"
        [$name, $code] = $this->inferViewNameAndCode($domain);

        // Register domain for stancl tenancy
        $tenant->domains()->firstOrCreate(['domain' => $domain]);

        // Create view (all views are equal, 'default' is just a name)
        $view = TenantView::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'domain' => $domain,
            'code' => $code,
        ]);

        // Create view folder structure
        $this->createViewFolders($tenant->id, $code);

        $this->info("  ✓ View '{$name}' ({$code}) created for {$domain}");
    }

    protected function inferViewNameAndCode(string $domain): array
    {
        // Extract subdomain if present (e.g., "admin.lapp.test" -> "admin")
        $parts = explode('.', $domain);
        
        // If domain starts with subdomain (has 3+ parts), use subdomain as name/code
        if (count($parts) >= 3) {
            $name = $parts[0];
            return [$name, $name]; // name and code are the same
        }
        
        // Otherwise, it's the main domain -> name it 'default'
        return ['default', 'default'];
    }

    protected function createViewFolders(string $tenantId, string $code): void
    {
        // Use consolidated structure: tenants/{tenant_id}/resources/views/tenants/{tenant_id}/{code}/
        $tenantBasePath = base_path("tenants/{$tenantId}");
        $viewsBasePath = "{$tenantBasePath}/resources/views";
        $tenantViewsPath = "{$viewsBasePath}/tenants/{$tenantId}";
        $viewPath = "{$tenantViewsPath}/{$code}";

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

        // Create tenant-specific views directory if it doesn't exist
        if (!File::exists($tenantViewsPath)) {
            File::makeDirectory($tenantViewsPath, 0755, true);
            $this->line("    Created folder: tenants/{$tenantId}/resources/views/tenants/{$tenantId}/");
        }

        // Create view code folder if it doesn't exist
        if (!File::exists($viewPath)) {
            File::makeDirectory($viewPath, 0755, true);
            $this->line("    Created folder: tenants/{$tenantId}/resources/views/tenants/{$tenantId}/{$code}/");
        }

        // Create a basic home.blade.php if it doesn't exist
        $homeViewPath = "{$viewPath}/home.blade.php";
        if (!File::exists($homeViewPath)) {
            $homeViewContent = $this->generateHomeView($tenantId, $code);
            File::put($homeViewPath, $homeViewContent);
            $this->line("    Created starter view: tenants/{$tenantId}/resources/views/tenants/{$tenantId}/{$code}/home.blade.php");
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

