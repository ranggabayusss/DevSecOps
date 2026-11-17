@extends('layouts.app')

@section('title', 'Manajemen Komunitas')

@section('content')
<x-page-header
    title="Manajemen Komunitas"
    description="Kelola komunitas dan ketua Unknown Rider."
>
    <x-slot:actions>
        <a href="{{ route('admin.communities.create') }}"
           class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            + Tambah Komunitas
        </a>
    </x-slot:actions>
</x-page-header>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if ($communities->isEmpty())
    <x-empty-state
        title="Belum ada komunitas"
        description="Silakan buat komunitas pertama Unknown Rider."
    >
        <a href="{{ route('admin.communities.create') }}"
           class="mt-5 inline-block rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            Buat Komunitas
        </a>
    </x-empty-state>
@else
    <div class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-800 bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama Komunitas</th>
                    <th class="px-4 py-3 font-medium">Ketua</th>
                    <th class="px-4 py-3 font-medium">Anggota</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                @foreach ($communities as $community)
                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-3.5 text-sm font-medium text-white">
                            {{ $community->name }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $community->leader?->name ?? 'Belum ditentukan' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $community->members_count }} anggota
                        </td>

                        <td class="px-4 py-3.5 text-right">
                            <a href="{{ route('admin.communities.members.index', $community->id) }}"
                               class="inline-block rounded-lg border border-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-gray-600 hover:text-white">
                                Kelola Anggota
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $communities->links() }}</div>
@endif
@endsection
