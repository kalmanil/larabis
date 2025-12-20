<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
        /* Prevent SVGs from expanding to 100% width - only expand if no explicit sizing */
        svg {
            display: inline-block;
            vertical-align: middle;
        }
        /* SVGs with explicit width/height should maintain their size */
        svg[width], svg[height] {
            max-width: none !important;
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>
</body>
</html>

