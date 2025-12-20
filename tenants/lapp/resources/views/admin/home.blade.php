@extends('layouts.app')

@section('title', 'Admin Home - Lapp')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="bg-purple-600 text-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-4xl font-bold mb-2">Lapp Admin Panel</h1>
            <p class="text-purple-100">Content Management System</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">{{ ucfirst($view->name ?? 'admin') }} View</h2>
            <p class="text-gray-600 mb-4">
                This is the <strong>{{ $view->name ?? 'admin' }}</strong> view for the <strong>{{ $tenant->id ?? 'lapp' }}</strong> tenant.
            </p>
            <div class="bg-purple-50 border border-purple-200 rounded p-4">
                <h3 class="font-semibold text-purple-900 mb-2">Current Context:</h3>
                <ul class="text-sm text-purple-800 space-y-1">
                    <li><strong>Tenant:</strong> {{ $tenant->id ?? 'lapp' }}</li>
                    <li><strong>View:</strong> {{ $view->name ?? 'admin' }}</li>
                    <li><strong>View Code:</strong> {{ $view->code ?? 'admin' }}</li>
                    <li><strong>Domain:</strong> {{ $view->domain ?? request()->getHost() }}</li>
                </ul>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-semibold mb-4">Quick Links</h2>
            <div class="space-y-2">
                <a href="/dashboard" class="block text-purple-600 hover:text-purple-800">
                    → Go to Dashboard
                </a>
                <a href="http://lapp.test:8000" class="block text-blue-600 hover:text-blue-800">
                    → Go to Default View (lapp.test)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

