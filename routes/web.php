<?php

use Illuminate\Support\Facades\Route;
use App\Features\Pages\Controllers\PageController;
use App\Helpers\TenancyHelper;

// Home route - handles both default and admin views
Route::get('/', [PageController::class, 'home']);

// Admin login route
Route::get('/login', [PageController::class, 'adminLogin'])->name('admin.login');
