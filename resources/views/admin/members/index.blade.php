@extends('layouts.app')

@section('title', 'Manajemen Anggota')

@section('content')
<x-page-header
    title="Manajemen Anggota"
    :description="'Komunitas '.$community->name.' · Ketua '.($community->leader->name ?? '-')"
>
    <x-slot:actions>
        <a href="{{ route('admin.communities.select') }}"
           class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
            Daftar Komunitas
        </a>

        <a href="{{ route('admin.communities.members.create', $community->id) }}"
           class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            + Tambah Anggota
        </a>
    </x-slot:actions>
</x-page-header>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@php
    $eligibleMembers = $members->getCollection()->filter(function ($membership) use ($community) {
        return $membership->status === 'active'
            && in_array($membership->user->role, ['member', 'ketua'], true)
            && $membership->user_id != $community->leader_id;
    });
@endphp

<section class="mb-6 rounded-xl border border-gray-800 bg-gray-900 p-6">
    <h2 class="font-semibold text-white">Pergantian Ketua Komunitas</h2>
    <p class="mt-1.5 text-sm text-gray-400">
        Pilih anggota aktif untuk menjadi ketua komunitas yang baru.
    </p>

    @if ($eligibleMembers->isNotEmpty())
        <form action="{{ route('admin.communities.leader.update', $community->id) }}"
              method="POST"
              class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end"
              onsubmit="return confirm('Yakin ingin mengganti ketua komunitas?')">
            @csrf
            @method('PUT')

            <div class="flex-1">
                <label for="user_id" class="mb-1.5 block text-sm font-medium text-gray-300">
                    Pilih Ketua Baru
                </label>

                <select
                    name="user_id"
                    id="user_id"
                    required
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
                    <option value="">-- Pilih Anggota --</option>

                    @foreach ($eligibleMembers as $membership)
                        <option value="{{ $membership->user_id }}" @selected(old('user_id') == $membership->user_id)>
                            {{ $membership->user->name }} ({{ $membership->user->email }})
                        </option>
                    @endforeach
                </select>

                @error('user_id')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
                Ganti Ketua
            </button>
        </form>
    @else
        <p class="mt-5 rounded-lg bg-gray-800/60 px-4 py-3 text-sm text-gray-400">
            Belum ada anggota aktif lain yang dapat ditunjuk sebagai ketua.
        </p>
    @endif
</section>

@if ($members->isEmpty())
    <x-empty-state
        title="Belum ada anggota"
        description="Tambahkan anggota pertama ke komunitas ini."
    >
        <a href="{{ route('admin.communities.members.create', $community->id) }}"
           class="mt-5 inline-block rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
            Tambah Anggota
        </a>
    </x-empty-state>
@else
    <div class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-800 bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">No.</th>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Nomor HP</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                @foreach ($members as $index => $membership)
                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-3.5 text-sm text-gray-500">
                            {{ ($members->firstItem() ?? 1) + $index }}
                        </td>

                        <td class="px-4 py-3.5 text-sm font-medium text-white">
                            {{ $membership->user->name }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $membership->user->email }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $membership->user->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5">
                            @if ($membership->user_id == $community->leader_id)
                                <span class="inline-flex items-center rounded-full border border-orange-500/30 bg-orange-500/10 px-2.5 py-0.5 text-xs font-medium text-orange-300">
                                    Ketua
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-gray-700 bg-gray-800 px-2.5 py-0.5 text-xs font-medium text-gray-300">
                                    {{ ucfirst($membership->user->role) }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5">
                            @switch($membership->status)
                                @case('active')
                                    <span class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-300">
                                        Aktif
                                    </span>
                                    @break
                                @case('pending')
                                    <span class="inline-flex items-center rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-xs font-medium text-amber-300">
                                        Menunggu
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="inline-flex items-center rounded-full border border-red-500/30 bg-red-500/10 px-2.5 py-0.5 text-xs font-medium text-red-300">
                                        Ditolak
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center rounded-full border border-gray-700 bg-gray-800 px-2.5 py-0.5 text-xs font-medium text-gray-300">
                                        {{ ucfirst($membership->status) }}
                                    </span>
                            @endswitch
                        </td>

                        <td class="px-4 py-3.5 text-right">
                            @if ($membership->user_id != $community->leader_id)
                                <form action="{{ route('admin.communities.members.destroy', [$community->id, $membership->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin mengeluarkan anggota ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-300 transition hover:bg-red-500/20">
                                        Keluarkan
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-500">Ketua komunitas</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $members->links() }}</div>
@endif
@endsection
