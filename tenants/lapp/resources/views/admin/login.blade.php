@extends('layouts.app')

@section('title', ($view_config['meta_title'] ?? 'Admin Login') . ' - ' . ($config['name'] ?? 'Lapp'))

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" style="background: linear-gradient(135deg, {{ $theme['background_color'] ?? '#f8fafc' }} 0%, {{ $theme['primary_color'] ?? '#6366f1' }}15 100%);">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="text-center mb-4">
                @if(isset($dashboard['custom_branding']['logo']))
                    <img src="{{ $dashboard['custom_branding']['logo'] }}" alt="{{ $dashboard['custom_branding']['company_name'] ?? 'Logo' }}" class="h-12 mx-auto mb-4">
                @endif
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold" style="color: {{ $theme['primary_color'] ?? '#6366f1' }}">
                Admin CMS Login
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ $config['name'] ?? 'Lapp' }} Administration Panel
                @if(isset($lapp_admin_config['custom_theme']))
                    <span class="block text-xs mt-1" style="color: {{ $theme['accent_color'] ?? '#10b981' }}">
                        {{ ucfirst($lapp_admin_config['custom_theme']) }} Theme
                    </span>
                @endif
            </p>
        </div>
        <form class="mt-8 space-y-6" action="#" method="POST">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Email address" value="admin@lapp.test">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Password" value="password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium" style="color: {{ $theme['primary_color'] ?? $branding['primary_color'] ?? '#3b82f6' }}">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 shadow-lg hover:shadow-xl transition"
                        style="background-color: {{ $theme['primary_color'] ?? $branding['primary_color'] ?? '#3b82f6' }}">
                    Sign in
                </button>
            </div>
            
            @if(isset($lapp_admin_config['enable_advanced_features']) && $lapp_admin_config['enable_advanced_features'])
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <p class="text-xs text-blue-800">
                        <strong>Advanced Features:</strong> Enabled for Lapp tenant
                    </p>
                </div>
            @endif

            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                <p class="text-sm text-yellow-800">
                    <strong>Mock Login:</strong> This is a demonstration login page. 
                    Use any credentials to proceed (authentication not implemented).
                </p>
            </div>
        </form>

        <div class="text-center">
            <a href="http://lapp.test:8000" class="text-sm" style="color: {{ $theme['primary_color'] ?? $branding['primary_color'] ?? '#3b82f6' }}">
                ← Back to {{ $config['name'] ?? 'Lapp' }} Home
            </a>
        </div>
    </div>
</div>
@endsection

