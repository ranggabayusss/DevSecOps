@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<x-page-header
    title="Dashboard Admin"
    description="Kelola komunitas, ketua, dan anggota Unknown Rider."
/>

<div class="grid gap-4 sm:grid-cols-2">
    <a href="{{ route('admin.communities.index') }}"
       class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
        <h2 class="font-semibold text-white transition group-hover:text-orange-400">
            Manajemen Komunitas
        </h2>
        <p class="mt-1.5 text-sm text-gray-400">
            Buat komunitas baru, tetapkan ketua, dan lihat ringkasan anggota.
        </p>
        <span class="mt-4 inline-block text-sm font-medium text-orange-400">
            Kelola komunitas &rarr;
        </span>
    </a>

    <a href="{{ route('admin.communities.select') }}"
       class="group rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
        <h2 class="font-semibold text-white transition group-hover:text-orange-400">
            Daftar Komunitas
        </h2>
        <p class="mt-1.5 text-sm text-gray-400">
            Pilih komunitas untuk melihat dan mengelola anggotanya.
        </p>
        <span class="mt-4 inline-block text-sm font-medium text-orange-400">
            Pilih komunitas &rarr;
        </span>
    </a>
</div>
@endsection
