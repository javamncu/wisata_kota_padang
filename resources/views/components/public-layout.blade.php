<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Wisata Kota Padang' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    <nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="h-11 w-auto">
                    </a>
                    <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                        <a href="{{ route('explore') }}" class="hover:text-emerald-700 {{ request()->routeIs('explore') ? 'text-emerald-700' : '' }}">Explore</a>
                        <a href="{{ route('nearby.index') }}" class="hover:text-emerald-700 {{ request()->routeIs('nearby.*') ? 'text-emerald-700' : '' }}">Sekitarku</a>
                        <a href="{{ route('quiz.index') }}" class="hover:text-emerald-700 {{ request()->routeIs('quiz.*') ? 'text-emerald-700' : '' }}">Kuis Preferensi</a>
                        <a href="{{ route('map.index') }}" class="hover:text-emerald-700 {{ request()->routeIs('map.*') ? 'text-emerald-700' : '' }}">Peta</a>
                        <a href="{{ route('blog.index') }}" class="hover:text-emerald-700 {{ request()->routeIs('blog.*') ? 'text-emerald-700' : '' }}">Blog</a>
                        <a href="{{ route('concierge.index') }}" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100 {{ request()->routeIs('concierge.*') ? 'bg-emerald-100' : '' }}">✨ Tanya AI</a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-600 hover:text-emerald-700">
                                    {{ Auth::user()->name }}
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('favorites.index')">Favorit Saya</x-dropdown-link>
                                <x-dropdown-link :href="route('reviews.mine')">Review Saya</x-dropdown-link>
                                <x-dropdown-link :href="route('dashboard')">Dashboard</x-dropdown-link>
                                <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                                @if (Auth::user()->isAdmin() && Route::has('admin.dashboard'))
                                    <x-dropdown-link :href="route('admin.dashboard')">Panel Admin</x-dropdown-link>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-emerald-700">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Daftar</a>
                    @endauth
                </div>

                <button @click="open = !open" class="md:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-cloak class="md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
            <a href="{{ route('explore') }}" class="block py-2 text-sm text-gray-700">Explore</a>
            <a href="{{ route('nearby.index') }}" class="block py-2 text-sm text-gray-700">Sekitarku</a>
            <a href="{{ route('quiz.index') }}" class="block py-2 text-sm text-gray-700">Kuis Preferensi</a>
            <a href="{{ route('map.index') }}" class="block py-2 text-sm text-gray-700">Peta</a>
            <a href="{{ route('blog.index') }}" class="block py-2 text-sm text-gray-700">Blog</a>
            <a href="{{ route('concierge.index') }}" class="block py-2 text-sm font-semibold text-emerald-700">✨ Tanya AI Concierge</a>
            <div class="border-t border-gray-100 pt-2">
                @auth
                    <a href="{{ route('favorites.index') }}" class="block py-2 text-sm text-gray-700">Favorit Saya</a>
                    <a href="{{ route('reviews.mine') }}" class="block py-2 text-sm text-gray-700">Review Saya</a>
                    <a href="{{ route('profile.edit') }}" class="block py-2 text-sm text-gray-700">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full text-left py-2 text-sm text-gray-700">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block py-2 text-sm text-gray-700">Masuk</a>
                    <a href="{{ route('register') }}" class="block py-2 text-sm font-semibold text-emerald-700">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                <span>{{ session('status') }}</span>
                <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        </div>
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid gap-8 md:grid-cols-3">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="h-14 w-auto">
                <p class="mt-3 text-sm text-gray-500">{{ setting('about_text') }}</p>
                <div class="mt-3 space-y-1 text-sm text-gray-500">
                    @if (setting('contact_email'))
                        <p>✉️ <a href="mailto:{{ setting('contact_email') }}" class="hover:text-emerald-700">{{ setting('contact_email') }}</a></p>
                    @endif
                    @if (setting('social_instagram'))
                        <p>📷 {{ setting('social_instagram') }}</p>
                    @endif
                </div>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Jelajahi</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('explore') }}" class="hover:text-emerald-700">Semua Destinasi</a></li>
                    <li><a href="{{ route('nearby.index') }}" class="hover:text-emerald-700">Wisata di Sekitarku</a></li>
                    <li><a href="{{ route('quiz.index') }}" class="hover:text-emerald-700">Kuis Preferensi</a></li>
                    <li><a href="{{ route('map.index') }}" class="hover:text-emerald-700">Peta Interaktif</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-emerald-700">Blog</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Tentang</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    <li><a href="{{ route('about') }}" class="hover:text-emerald-700">Tentang & Kontak</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-100 py-4 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Wisata Kota Padang.
        </div>
    </footer>

    <style>[x-cloak]{display:none!important;}</style>
    @stack('scripts')
</body>
</html>
