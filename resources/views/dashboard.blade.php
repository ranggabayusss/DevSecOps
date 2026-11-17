@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header
    title="Dashboard"
    description="Pantau keanggotaan dan aktivitas komunitas Unknown Rider dari satu tempat."
/>

@if ($ledCommunity)
    <section class="mb-6 rounded-xl border border-orange-500/30 bg-gray-900 p-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-orange-400">
            Komunitas yang kamu pimpin
        </p>

        <h2 class="mt-2 text-xl font-semibold text-white">{{ $ledCommunity->name }}</h2>

        <p class="mt-1.5 text-sm text-gray-400">
            Tinjau dan proses permintaan anggota yang ingin bergabung.
        </p>

        <a href="{{ route('ketua.communities.requests.index', $ledCommunity->id) }}"
           class="mt-5 inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            Lihat Permintaan Bergabung
        </a>
    </section>
@endif

<div class="grid gap-4 sm:grid-cols-2">
    <a href="{{ route('communities.index') }}"
       class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
        <h2 class="font-semibold text-white transition group-hover:text-orange-400">
            Komunitas
        </h2>
        <p class="mt-1.5 text-sm text-gray-400">
            Lihat komunitas yang tersedia dan status keanggotaanmu.
        </p>
        <span class="mt-4 inline-block text-sm font-medium text-orange-400">
            Buka daftar &rarr;
        </span>
    </a>

    @if (Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
            <h2 class="font-semibold text-white transition group-hover:text-orange-400">
                Panel Admin
            </h2>
            <p class="mt-1.5 text-sm text-gray-400">
                Kelola komunitas, ketua, dan anggota.
            </p>
            <span class="mt-4 inline-block text-sm font-medium text-orange-400">
                Buka panel &rarr;
            </span>
        </a>
    @else
        <div class="rounded-xl border border-gray-800 bg-gray-900 p-5">
            <h2 class="font-semibold text-white">Akun Saya</h2>
            <p class="mt-1.5 text-sm text-gray-400">
                Role akun: <span class="text-gray-200">{{ ucfirst(Auth::user()->role) }}</span>
            </p>
        </div>
    @endif
</div>
@endsection
