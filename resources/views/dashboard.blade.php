@php
    $user = auth()->user();
    $favCount = $user->favorites()->count();
    $reviewCount = $user->reviews()->count();
@endphp

<x-public-layout title="Dashboard — Wisata Kota Padang">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900">Halo, {{ $user->name }} 👋</h1>
        <p class="mt-1 text-gray-500">Ringkasan aktivitasmu.</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('favorites.index') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md">
                <p class="text-sm text-gray-500">Destinasi favorit</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $favCount }}</p>
                <p class="mt-2 text-sm font-medium text-emerald-700">Lihat favorit &rarr;</p>
            </a>
            <a href="{{ route('reviews.mine') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md">
                <p class="text-sm text-gray-500">Ulasan ditulis</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $reviewCount }}</p>
                <p class="mt-2 text-sm font-medium text-emerald-700">Lihat review saya &rarr;</p>
            </a>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('explore') }}" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Jelajahi destinasi</a>
            <a href="{{ route('quiz.index') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Kuis preferensi</a>
            <a href="{{ route('profile.edit') }}" class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit profil</a>
        </div>
    </div>
</x-public-layout>
