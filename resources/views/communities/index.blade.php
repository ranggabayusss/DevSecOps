@extends('layouts.app')

@section('title', 'Daftar Komunitas')

@section('content')
<x-page-header
    title="Daftar Komunitas"
    description="Temukan komunitas dan ajukan permintaan bergabung."
/>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if ($communities->isEmpty())
    <x-empty-state
        title="Belum ada komunitas"
        description="Saat ini belum tersedia komunitas untuk bergabung."
    />
@else
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($communities as $community)
            <article class="flex flex-col rounded-xl border border-gray-800 bg-gray-900 p-5">
                <h2 class="text-lg font-semibold text-white">{{ $community->name }}</h2>

                <p class="mt-2 flex-1 text-sm leading-relaxed text-gray-400">
                    {{ $community->description ?? 'Belum ada deskripsi.' }}
                </p>

                <div class="mt-4 flex items-center justify-between gap-3 border-t border-gray-800 pt-4">
                    <p class="text-xs text-gray-500">
                        Ketua
                        <span class="ml-1 text-gray-300">{{ $community->leader->name ?? '-' }}</span>
                        <span class="mx-1.5 text-gray-700">|</span>
                        {{ $community->members_count }} anggota
                    </p>

                    @if ($userMembership)
                        @if ($userMembership->community_id == $community->id)
                            @if ($userMembership->status === 'active')
                                <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">
                                    Sudah bergabung
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-medium text-amber-300">
                                    Menunggu persetujuan
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center rounded-full border border-gray-700 bg-gray-800 px-3 py-1 text-xs font-medium text-gray-400">
                                Sudah punya komunitas
                            </span>
                        @endif
                    @else
                        <form action="{{ route('communities.join', $community->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-500">
                                Ajukan Bergabung
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">{{ $communities->links() }}</div>
@endif
@endsection
