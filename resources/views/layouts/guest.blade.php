<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans text-gray-900 antialiased {{ request()->routeIs('login') ? 'login-page' : '' }}">
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center overflow-hidden px-4 py-8 sm:py-10 bg-gradient-to-br from-sky-100 via-blue-50 to-cyan-100">
            <svg class="pointer-events-none absolute -left-10 top-12 h-40 w-40 text-sky-300/70" viewBox="0 0 160 160" fill="none" aria-hidden="true"><path d="M37 136C49 99 67 73 105 31" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="M67 92C46 82 33 66 39 52C58 55 70 69 67 92ZM81 72C80 51 89 34 104 29C112 47 103 64 81 72ZM49 113C29 112 15 101 12 86C31 82 46 92 49 113Z" fill="currentColor"/></svg>
            <svg class="pointer-events-none absolute -right-8 bottom-8 h-48 w-48 text-cyan-300/70" viewBox="0 0 190 190" fill="none" aria-hidden="true"><path d="M28 160C70 143 96 110 132 44" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="M71 132C45 130 29 116 30 98C51 96 68 109 71 132ZM105 93C93 70 98 48 115 38C129 57 124 78 105 93ZM54 151C38 164 20 164 9 153C21 137 38 137 54 151Z" fill="currentColor"/></svg>
            <div class="relative z-10 w-full sm:max-w-xl rounded-[2rem] bg-gradient-to-br from-blue-500 via-sky-400 to-cyan-400 p-[3px] shadow-2xl shadow-blue-300/50">
                <div class="login-card w-full rounded-[1.85rem] bg-white px-7 py-9 sm:px-12 sm:py-11">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
