@extends('layouts.app')

@section('title', 'Tambah Komunitas')

@section('content')
<x-page-header
    title="Tambah Komunitas"
    description="Masukkan informasi komunitas dan tentukan ketuanya."
/>

<div class="max-w-2xl">
    <div class="rounded-xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
        @if ($errors->any())
            <x-alert type="error">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form action="{{ route('admin.communities.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-300">Nama Komunitas</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Contoh: Unknown Rider Bandung"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-gray-300">Deskripsi</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Deskripsi komunitas..."
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="logo" class="mb-1.5 block text-sm font-medium text-gray-300">
                    Logo <span class="font-normal text-gray-500">(opsional)</span>
                </label>
                <input
                    id="logo"
                    type="text"
                    name="logo"
                    value="{{ old('logo') }}"
                    placeholder="Path atau URL logo"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="leader_id" class="mb-1.5 block text-sm font-medium text-gray-300">Pilih Ketua Komunitas</label>
                <select
                    id="leader_id"
                    name="leader_id"
                    required
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
                    <option value="">-- Pilih Pengguna --</option>

                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(old('leader_id') == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-500">
                    Hanya pengguna dengan role Member yang dapat dipilih.
                </p>
            </div>

            <div class="flex flex-col gap-2 pt-1 sm:flex-row">
                <button type="submit"
                        class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                    Simpan Komunitas
                </button>

                <a href="{{ route('admin.communities.index') }}"
                   class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-center text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
