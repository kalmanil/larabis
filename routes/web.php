<?php

use Illuminate\Support\Facades\Route;
use App\Models\TenantView;
use App\Helpers\TenancyHelper;

Route::get('/', function () {
    $view = TenancyHelper::currentView();
    $tenant = TenancyHelper::currentTenant();
    
    // Use tenant-specific view helper
    return TenancyHelper::view('home', [
        'tenant' => $tenant,
        'view' => $view,
    ]);
});

Route::get('/dashboard', function () {
    $view = TenancyHelper::currentView();
    $tenant = TenancyHelper::currentTenant();
    
    // Use tenant-specific view helper for dashboard
    return TenancyHelper::view('dashboard', [
        'tenant' => $tenant,
        'view' => $view,
    ]);
});
