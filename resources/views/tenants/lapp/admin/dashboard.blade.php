@extends('layouts.app')

@section('title', 'Admin Dashboard - Lapp')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-purple-600 text-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-4xl font-bold mb-2">Lapp Admin Dashboard</h1>
            <p class="text-purple-100">Content Management System for Lapp</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-2">Tenant Information</h3>
                <p class="text-gray-600 text-sm mb-4">Current tenant details</p>
                <div class="space-y-2 text-sm">
                    <div><strong>ID:</strong> {{ $tenant->id ?? 'lapp' }}</div>
                    <div><strong>Created:</strong> {{ $tenant->created_at ?? 'N/A' }}</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-2">View Information</h3>
                <p class="text-gray-600 text-sm mb-4">Current view context</p>
                <div class="space-y-2 text-sm">
                    <div><strong>Name:</strong> {{ $view->name ?? 'admin' }}</div>
                    <div><strong>Code:</strong> {{ $view->code ?? 'admin' }}</div>
                    <div><strong>Domain:</strong> {{ $view->domain ?? request()->getHost() }}</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-semibold mb-2">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="/" class="block text-blue-600 hover:text-blue-800 text-sm">View Site</a>
                    <a href="http://lapp.test:8000" class="block text-blue-600 hover:text-blue-800 text-sm">
                        Go to Default View →
                    </a>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-semibold mb-4">Admin Features</h2>
            <p class="text-gray-600 mb-4">
                This is the admin/CMS view for Lapp. Here you can manage content, users, settings, etc.
            </p>
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                <p class="text-sm text-yellow-800">
                    <strong>Note:</strong> This is a separate view from the default site. 
                    You can have different layouts, features, and functionality here.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

