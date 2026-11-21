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

@if (! Auth::user()->hasCompleteMemberProfile())
    <x-alert type="warning" class="mb-6">
        Profil anggota belum lengkap. Lengkapi nama motor dan plat nomor agar dapat melakukan RSVP acara.
        <a href="{{ route('profile.edit') }}" class="font-semibold underline">Lengkapi sekarang</a>.
    </x-alert>
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

    <a href="{{ route('events.index') }}"
       class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
        <h2 class="font-semibold text-white transition group-hover:text-orange-400">
            Acara &amp; RSVP
        </h2>
        <p class="mt-1.5 text-sm text-gray-400">
            Cek jadwal kegiatan dan konfirmasi kehadiranmu.
        </p>
        <span class="mt-4 inline-block text-sm font-medium text-orange-400">
            Lihat acara &rarr;
        </span>
    </a>

    @if (Auth::user()->role === 'admin')
        <a href="{{ route('admin.dashboard') }}"
           class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
            <h2 class="font-semibold text-white transition group-hover:text-orange-400">
                Panel Admin
            </h2>
            <p class="mt-1.5 text-sm text-gray-400">
                Kelola komunitas, ketua, anggota, dan role pengguna.
            </p>
            <span class="mt-4 inline-block text-sm font-medium text-orange-400">
                Buka panel &rarr;
            </span>
        </a>
    @endif

    <a href="{{ route('profile.edit') }}"
       class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
        <h2 class="font-semibold text-white transition group-hover:text-orange-400">
            Profil Anggota
        </h2>
        <p class="mt-1.5 text-sm text-gray-400">
            Nomor anggota:
            <span class="font-semibold text-orange-300">{{ Auth::user()->member_number ?? '-' }}</span>
            <span class="mx-1 text-gray-700">|</span>
            {{ ucfirst(Auth::user()->role) }}
        </p>
        <span class="mt-4 inline-block text-sm font-medium text-orange-400">
            Kelola profil &rarr;
        </span>
    </a>
</div>
@endsection
