@extends('layouts.app')

@section('title', ($config['name'] ?? 'Lapp') . ' - ' . ($config['tagline'] ?? 'Welcome'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold" style="color: {{ $branding['primary_color'] ?? '#3b82f6' }}">
                        {{ $config['name'] ?? 'Lapp' }}
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-700 hover:text-gray-900">Home</a>
                    <a href="http://admin.lapp.test:8001" class="px-4 py-2 rounded-md text-white font-medium" style="background-color: {{ $branding['primary_color'] ?? '#3b82f6' }}">
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center">
            <h1 class="text-5xl font-bold text-gray-900 mb-6">
                Welcome to {{ $config['name'] ?? 'Lapp' }}
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                {{ $config['tagline'] ?? 'Your Business Solution' }}
            </p>
            <div class="flex justify-center space-x-4">
                <a href="#features" class="px-8 py-3 rounded-lg text-white font-semibold shadow-lg hover:shadow-xl transition" style="background-color: {{ $branding['primary_color'] ?? '#3b82f6' }}">
                    Get Started
                </a>
                <a href="#about" class="px-8 py-3 rounded-lg border-2 font-semibold hover:bg-gray-50 transition" style="border-color: {{ $branding['primary_color'] ?? '#3b82f6' }}; color: {{ $branding['primary_color'] ?? '#3b82f6' }}">
                    Learn More
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Features</h2>
            <p class="text-gray-600">Everything you need to succeed</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: {{ $branding['primary_color'] ?? '#3b82f6' }}20">
                    <svg width="24" height="24" style="color: {{ $branding['primary_color'] ?? '#3b82f6' }}; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Fast & Reliable</h3>
                <p class="text-gray-600">Built for performance and reliability</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: {{ $branding['secondary_color'] ?? '#8b5cf6' }}20">
                    <svg width="24" height="24" style="color: {{ $branding['secondary_color'] ?? '#8b5cf6' }}; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure</h3>
                <p class="text-gray-600">Enterprise-grade security features</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="w-12 h-12 rounded-lg mb-4 flex items-center justify-center" style="background-color: {{ $branding['accent_color'] ?? '#10b981' }}20">
                    <svg width="24" height="24" style="color: {{ $branding['accent_color'] ?? '#10b981' }}; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">User Friendly</h3>
                <p class="text-gray-600">Intuitive interface for everyone</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} {{ $config['name'] ?? 'Lapp' }}. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
@endsection

