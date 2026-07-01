@props(['title' => 'Admin', 'heading' => null])

@php
    $nav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '📊', 'pattern' => 'admin.dashboard'],
        ['route' => 'admin.destinations.index', 'label' => 'Destinasi', 'icon' => '📍', 'pattern' => 'admin.destinations.*'],
        ['route' => 'admin.categories.index', 'label' => 'Kategori', 'icon' => '🗂️', 'pattern' => 'admin.categories.*'],
        ['route' => 'admin.tags.index', 'label' => 'Tag', 'icon' => '🏷️', 'pattern' => 'admin.tags.*'],
        ['route' => 'admin.articles.index', 'label' => 'Artikel', 'icon' => '📝', 'pattern' => 'admin.articles.*'],
        ['route' => 'admin.users.index', 'label' => 'User', 'icon' => '👥', 'pattern' => 'admin.users.*'],
        ['route' => 'admin.reviews.index', 'label' => 'Moderasi Review', 'icon' => '⭐', 'pattern' => 'admin.reviews.*'],
        ['route' => 'admin.questions.index', 'label' => 'Tanya Jawab', 'icon' => '💬', 'pattern' => 'admin.questions.*'],
        ['route' => 'admin.concierge.index', 'label' => 'AI Concierge', 'icon' => '🤖', 'pattern' => 'admin.concierge.*'],
        ['route' => 'admin.settings.edit', 'label' => 'Pengaturan', 'icon' => '⚙️', 'pattern' => 'admin.settings.*'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Admin Wisata Padang</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div x-data="{ sidebar: false }" class="min-h-screen lg:flex">
        {{-- Sidebar --}}
        <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-64 transform bg-gray-900 text-gray-300 transition-transform lg:static lg:translate-x-0">
            <div class="flex h-16 items-center gap-2 px-4 font-bold text-white">
                <span class="inline-flex items-center rounded-lg bg-white px-1.5 py-1">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ setting('site_name') }}" class="h-8 w-auto">
                </span>
                Admin Panel
            </div>
            <nav class="mt-4 space-y-1 px-3">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                              {{ request()->routeIs($item['pattern']) ? 'bg-emerald-600 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                        <span>{{ $item['icon'] }}</span> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="absolute bottom-0 w-full border-t border-gray-800 p-3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-800 hover:text-white">🌐 Lihat situs</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-800 hover:text-white">🚪 Keluar</button>
                </form>
            </div>
        </aside>

        <div x-show="sidebar" @click="sidebar = false" x-cloak class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

        {{-- Main --}}
        <div class="flex-1">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebar = !sidebar" class="lg:hidden rounded-md p-2 text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $heading ?? $title }}</h1>
                </div>
                <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
            </header>

            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="mx-4 mt-4 sm:mx-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-4 mt-4 sm:mx-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <main class="p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <style>[x-cloak]{display:none!important;}</style>
    @stack('scripts')
</body>
</html>
