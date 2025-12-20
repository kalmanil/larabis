# Quick Start

## 1. Install Dependencies

```bash
cd larabis
composer install
npm install
```

## 2. Run Migrations

```bash
php artisan migrate
```

## 3. Create Tenant

```bash
# Create tenant with multiple views
php artisan tenant:create lapp \
    --domains=lapp.test \
    --domains=admin.lapp.test
```

This automatically creates:
- Tenant database
- View folders: `tenants/lapp/resources/views/tenants/lapp/default/` and `tenants/lapp/resources/views/tenants/lapp/admin/`
- Starter `home.blade.php` files
- Tenant directory structure: `tenants/lapp/`

## 4. Configure Hosts

Add to `/etc/hosts` (Linux/WSL) or `C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1 lapp.test
127.0.0.1 admin.lapp.test
```

## 5. Start Development

```bash
# Option 1: Start both servers with one command (recommended)
cd ..
./run-lapp-dev.sh

# Option 2: Start servers separately
# Terminal 1: Default view
cd lapp.test
bash run-dev.sh

# Terminal 2: Admin view
cd admin.lapp.test
bash run-dev.sh
```

Access:
- Default: http://lapp.test:8000
- Admin: http://admin.lapp.test:8001

## Usage

### Basic Route Example

```php
// In routes/web.php
use App\Helpers\TenancyHelper;

Route::get('/', function () {
    return TenancyHelper::view('home', [
        'tenant' => TenancyHelper::currentTenant(),
        'view' => TenancyHelper::currentView(),
    ]);
});

Route::get('/dashboard', function () {
    return TenancyHelper::view('dashboard', [
        'tenant' => TenancyHelper::currentTenant(),
        'view' => TenancyHelper::currentView(),
    ]);
});

// ⚠️ IMPORTANT: Only pass the view name (without view code prefix)
// For 'login.blade.php' in admin view, use:
Route::get('/login', function () {
    return TenancyHelper::view('login', $data);  // ✅ Correct
    // NOT: TenancyHelper::view('admin.login', $data);  // ❌ Wrong
});
```

### Conditional Content Based on View

```php
Route::get('/content', function () {
    $view = TenancyHelper::currentView();
    
    // Check if we're in admin view
    if (TenancyHelper::isAdminView()) {
        // Admin-specific logic
    }
    
    // Or check for specific view code
    if (TenancyHelper::isViewCode('api')) {
        // API-specific logic
    }
    
    return TenancyHelper::view('content', [
        'tenant' => TenancyHelper::currentTenant(),
        'view' => $view,
    ]);
});
```

## Adding Views

```bash
# Add view to existing tenant
php artisan tenant:view lapp api.lapp.test --name=api --code=api
```
