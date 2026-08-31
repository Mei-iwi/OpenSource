<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak] { display: none !important; }</style>
    <title>@yield('title', config('app.name', 'Luna HR'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen lg:flex">
        <aside class="hidden w-72 shrink-0 border-r border-slate-200 bg-white lg:block">@include('partials.sidebar')</aside>
        <div class="min-w-0 flex-1">
            @include('partials.navbar')
            <div x-show="mobileMenuOpen" x-cloak class="border-b border-slate-200 bg-white lg:hidden">
                @include('partials.sidebar')
            </div>
            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @include('partials.flash')
                @if (isset($slot)){{ $slot }}@else @yield('content')@endif
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
