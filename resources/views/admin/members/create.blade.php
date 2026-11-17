@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
<x-page-header
    title="Tambah Anggota"
    :description="'Komunitas '.$community->name"
>
    <x-slot:actions>
        <a href="{{ route('admin.communities.members.index', $community->id) }}"
           class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
            Kembali
        </a>
    </x-slot:actions>
</x-page-header>

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

        @if ($users->isEmpty())
            <x-empty-state
                title="Tidak ada pengguna tersedia"
                description="Semua pengguna dengan role Member sudah bergabung atau belum tersedia."
            />
        @else
            <form action="{{ route('admin.communities.members.store', $community->id) }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="user_id" class="mb-1.5 block text-sm font-medium text-gray-300">
                        Pilih Anggota
                    </label>

                    <select
                        name="user_id"
                        id="user_id"
                        required
                        class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                    >
                        <option value="">-- Pilih Pengguna --</option>

                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-2 pt-1 sm:flex-row">
                    <button type="submit"
                            class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                        Tambahkan Anggota
                    </button>

                    <a href="{{ route('admin.communities.members.index', $community->id) }}"
                       class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-center text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
                        Batal
                    </a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
