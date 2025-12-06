@extends('layouts.app')

@section('title', 'Home - Lapp')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">Welcome to Lapp</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Default View</h2>
            <p class="text-gray-600 mb-4">
                This is the default/public view for the <strong>lapp</strong> tenant.
            </p>
            
            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <h3 class="font-semibold text-blue-900 mb-2">Current Context:</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>Tenant:</strong> {{ $tenant->id ?? 'lapp' }}</li>
                    <li><strong>View:</strong> {{ $view->name ?? 'default' }}</li>
                    <li><strong>Code:</strong> {{ $view->code ?? 'default' }}</li>
                    <li><strong>Domain:</strong> {{ $view->domain ?? request()->getHost() }}</li>
                </ul>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-semibold mb-4">Quick Links</h2>
            <div class="space-y-2">
                <a href="/" class="block text-blue-600 hover:text-blue-800">Home</a>
                <a href="http://admin.lapp.test:8001" class="block text-purple-600 hover:text-purple-800">
                    Go to Admin Panel →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

