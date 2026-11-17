@extends('layouts.guest')

@section('title', 'Daftar Komunitas')

@section('content')
<div class="w-full max-w-5xl mx-auto rounded-2xl border border-gray-800 bg-gray-900 p-8 shadow-xl">

    <div class="mb-8">
        <h2 class="text-2xl font-bold">Daftar Komunitas</h2>
        <p class="mt-2 text-gray-400">
            Pilih komunitas untuk melihat dan mengelola anggotanya.
        </p>
    </div>

    @if($communities->isEmpty())
        <div class="rounded-xl border border-gray-700 bg-gray-800 p-8 text-center">
            <h3 class="text-lg font-semibold">Belum Ada Komunitas</h3>
            <p class="mt-2 text-gray-400">
                Silakan buat komunitas terlebih dahulu melalui Manajemen Komunitas.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach($communities as $community)
                <a href="{{ route('admin.communities.members.index', $community->id) }}"
                   class="block rounded-xl border border-gray-700 bg-gray-800 p-5 transition hover:border-orange-500">
                    <h3 class="text-lg font-bold text-orange-400">
                        {{ $community->name }}
                    </h3>

                    <p class="mt-2 text-sm text-gray-400">
                        Ketua: {{ $community->leader?->name ?? 'Belum ditentukan' }}
                    </p>

                    <p class="text-sm text-gray-400">
                        {{ $community->members_count }} anggota
                    </p>

                    <span class="mt-4 inline-block text-sm font-semibold text-blue-400">
                        Kelola Anggota &rarr;
                    </span>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $communities->links() }}</div>
    @endif

    <div class="mt-8 flex items-center justify-between gap-4">
        <a href="{{ route('admin.dashboard') }}"
           class="text-blue-400 hover:text-blue-300">
            ← Kembali ke Dashboard Admin
        </a>

        <a href="{{ route('admin.communities.index') }}"
           class="text-blue-400 hover:text-blue-300">
            Manajemen Komunitas →
        </a>
    </div>

</div>
@endsection
