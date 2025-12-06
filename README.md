# Larabis - Two-Level Multi-Tenancy

Larabis extends Laravel with a two-level tenancy system using `stancl/tenancy`:

- **Level 1: Tenants** - Separate databases per tenant
- **Level 2: Views** - Multiple views per tenant (e.g., `default`, `admin`, `api`)

## Quick Start

```bash
# Create tenant with views
php artisan tenant:create lapp --domains=lapp.test --domains=admin.lapp.test

# Start dev servers
cd lapp.test && bash run-dev.sh
cd admin.lapp.test && bash run-dev.sh
```

## Documentation

- [Quick Start](QUICK_START.md) - Get up and running
- [Tenant Structure](TENANT_STRUCTURE.md) - View organization
- [View Management](VIEW_MANAGEMENT.md) - Managing tenant views

## Key Features

- Domain-based routing with automatic tenant/view detection
- Tenant-specific views: `resources/views/tenants/{tenant_id}/{code}/`
- Automatic folder creation when adding views
- Helper methods: `TenancyHelper::currentTenant()`, `TenancyHelper::currentView()`
