@extends('layouts.app')

@section('title', 'Daftar Komunitas')

@section('content')
<x-page-header
    title="Daftar Komunitas"
    description="Pilih komunitas untuk melihat dan mengelola anggotanya."
>
    <x-slot:actions>
        <a href="{{ route('admin.communities.index') }}"
           class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
            Manajemen Komunitas
        </a>
    </x-slot:actions>
</x-page-header>

@if ($communities->isEmpty())
    <x-empty-state
        title="Belum ada komunitas"
        description="Silakan buat komunitas terlebih dahulu melalui Manajemen Komunitas."
    >
        <a href="{{ route('admin.communities.create') }}"
           class="mt-5 inline-block rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            Buat Komunitas
        </a>
    </x-empty-state>
@else
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($communities as $community)
            <a href="{{ route('admin.communities.members.index', $community->id) }}"
               class="group flex flex-col rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/60">
                <h2 class="font-semibold text-white transition group-hover:text-orange-400">
                    {{ $community->name }}
                </h2>

                <p class="mt-1.5 text-sm text-gray-400">
                    Ketua: {{ $community->leader?->name ?? 'Belum ditentukan' }}
                    <span class="mx-1.5 text-gray-700">|</span>
                    {{ $community->members_count }} anggota
                </p>

                <span class="mt-4 text-sm font-medium text-orange-400">
                    Kelola Anggota &rarr;
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $communities->links() }}</div>
@endif
@endsection
