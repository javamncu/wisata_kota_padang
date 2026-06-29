<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('site_name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-emerald-50 via-white to-white px-4 py-10">
            {{-- subtle decorative blobs --}}
            <div class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-100/50 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-teal-100/50 blur-3xl"></div>

            <a href="{{ url('/') }}" class="relative mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="h-28 w-auto drop-shadow-sm">
            </a>

            <div class="relative w-full sm:max-w-md rounded-2xl bg-white px-8 py-8 shadow-sm ring-1 ring-gray-100">
                {{ $slot }}
            </div>

            <a href="{{ url('/') }}" class="relative mt-6 text-sm text-gray-400 transition hover:text-emerald-700">← Kembali ke beranda</a>
        </div>
    </body>
</html>
