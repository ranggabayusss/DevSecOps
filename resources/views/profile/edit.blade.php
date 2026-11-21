@extends('layouts.app')

@section('title', 'Profil Anggota')

@section('content')
<x-page-header
    title="Profil Anggota"
    description="Perbarui data profil dan kendaraanmu. Nomor anggota dan role hanya dapat diubah oleh sistem atau Admin."
/>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert type="error">
        <ul class="space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    <div class="rounded-xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-300">Nama lengkap</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user->name) }}"
                    required
                    maxlength="100"
                    autocomplete="name"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-300">
                    Nomor telepon <span class="font-normal text-gray-500">(opsional)</span>
                </label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="{{ old('phone', $user->phone) }}"
                    maxlength="20"
                    autocomplete="tel"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="motorcycle_type" class="mb-1.5 block text-sm font-medium text-gray-300">Nama / tipe motor</label>
                <input
                    id="motorcycle_type"
                    name="motorcycle_type"
                    type="text"
                    value="{{ old('motorcycle_type', $user->motorcycle_type) }}"
                    required
                    maxlength="100"
                    placeholder="Contoh: Honda CB150R"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="license_plate" class="mb-1.5 block text-sm font-medium text-gray-300">Plat nomor</label>
                <input
                    id="license_plate"
                    name="license_plate"
                    type="text"
                    value="{{ old('license_plate', $user->license_plate) }}"
                    required
                    maxlength="20"
                    placeholder="Contoh: D 1234 ABC"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm uppercase text-gray-100 placeholder:normal-case placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <button type="submit"
                    class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                Simpan Profil
            </button>
        </form>
    </div>

    <aside class="h-fit rounded-xl border border-gray-800 bg-gray-900 p-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Kartu Anggota</p>

        <p class="mt-3 text-2xl font-bold tracking-tight text-orange-400">
            {{ $user->member_number ?? 'Belum tersedia' }}
        </p>

        <dl class="mt-5 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-gray-500">Role</dt>
                <dd class="font-medium text-gray-200">{{ ucfirst($user->role) }}</dd>
            </div>

            <div class="flex items-center justify-between gap-3">
                <dt class="text-gray-500">Email</dt>
                <dd class="truncate font-medium text-gray-200">{{ $user->email }}</dd>
            </div>

            <div class="flex items-center justify-between gap-3">
                <dt class="text-gray-500">Komunitas</dt>
                <dd class="font-medium text-gray-200">{{ $membership?->community?->name ?? '-' }}</dd>
            </div>

            <div class="flex items-center justify-between gap-3">
                <dt class="text-gray-500">Status</dt>
                <dd class="font-medium text-gray-200">{{ $membership ? ucfirst($membership->status) : '-' }}</dd>
            </div>
        </dl>

        <p class="mt-6 rounded-lg bg-gray-800/60 px-3.5 py-3 text-xs leading-relaxed text-gray-400">
            Nomor anggota, role, dan email tidak dapat diubah melalui form ini untuk mencegah manipulasi data.
        </p>
    </aside>
</div>
@endsection
