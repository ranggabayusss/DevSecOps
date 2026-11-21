@extends('layouts.app')

@section('title', 'Manajemen Anggota')

@section('content')
<x-page-header
    title="Manajemen Anggota"
    description="Kelola nomor anggota, kendaraan, dan role seluruh pengguna."
>
    <x-slot:actions>
        <a href="{{ route('admin.dashboard') }}"
           class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
            Dashboard Admin
        </a>
    </x-slot:actions>
</x-page-header>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

<form action="{{ route('admin.users.index') }}" method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
    <input
        type="search"
        name="search"
        value="{{ $search }}"
        maxlength="100"
        placeholder="Cari nama, email, nomor anggota, atau plat nomor"
        class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
    >

    <button type="submit"
            class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
        Cari
    </button>
</form>

@if ($users->isEmpty())
    <x-empty-state
        title="Tidak ada pengguna"
        description="Belum ada pengguna yang cocok dengan pencarian."
    />
@else
    <div class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-800 bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nomor Anggota</th>
                    <th class="px-4 py-3 font-medium">Nama &amp; Email</th>
                    <th class="px-4 py-3 font-medium">Motor</th>
                    <th class="px-4 py-3 font-medium">Plat Nomor</th>
                    <th class="px-4 py-3 font-medium">Komunitas</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                @foreach ($users as $user)
                    @php
                        $membership = $user->communityMemberships->first();
                    @endphp

                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-3.5 text-sm font-medium text-orange-300">
                            {{ $user->member_number ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5">
                            <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $user->email }}</p>
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $user->motorcycle_type ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $user->license_plate ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $membership?->community?->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5">
                            @if ((int) $user->id === (int) Auth::id())
                                <span class="inline-flex items-center rounded-full border border-orange-500/30 bg-orange-500/10 px-2.5 py-0.5 text-xs font-medium text-orange-300">
                                    {{ ucfirst($user->role) }} (akun kamu)
                                </span>
                            @else
                                <form action="{{ route('admin.users.role.update', $user) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')

                                    <select
                                        name="role"
                                        aria-label="Role {{ $user->name }}"
                                        class="rounded-lg border border-gray-800 bg-gray-950 px-2.5 py-1.5 text-xs text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                                    >
                                        @foreach (['member' => 'Member', 'ketua' => 'Ketua', 'admin' => 'Admin'] as $value => $label)
                                            <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <button type="submit"
                                            class="rounded-lg border border-gray-700 px-2.5 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-orange-500/50 hover:text-orange-300">
                                        Simpan
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection
