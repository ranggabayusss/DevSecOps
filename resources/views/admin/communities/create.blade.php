@extends('layouts.guest')

@section('title', 'Tambah Komunitas')

@section('content')
<div class="w-full max-w-2xl mx-auto rounded-2xl border border-gray-800 bg-gray-900 p-8 shadow-xl">

    <h2 class="text-2xl font-bold">Tambah Komunitas</h2>

    <p class="mt-2 mb-8 text-gray-400">
        Masukkan informasi komunitas dan tentukan Ketua.
    </p>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-green-800 bg-green-950/50 p-4 text-sm text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-800 bg-red-950/50 p-4 text-red-300">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($users->isEmpty())
        <div class="rounded-lg border border-yellow-800 bg-yellow-950/40 p-4 text-sm text-yellow-200">
            Belum ada member yang bisa dijadikan Ketua karena semua member sudah tergabung di komunitas lain.
            Buat akun calon Ketua baru di bawah ini, lalu pilih akun tersebut sebagai Ketua.
        </div>
    @else
        <form action="{{ route('admin.communities.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <label class="mb-2 block font-medium">Nama Komunitas</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Contoh: Unknown Rider Bandung"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                >
            </div>

            <div class="mb-5">
                <label class="mb-2 block font-medium">Deskripsi</label>

                <textarea
                    name="description"
                    rows="4"
                    placeholder="Deskripsi komunitas..."
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                >{{ old('description') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="mb-2 block font-medium">Logo (Opsional)</label>

                <input
                    type="text"
                    name="logo"
                    value="{{ old('logo') }}"
                    placeholder="Path atau URL logo"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                >
            </div>

            <div class="mb-8">
                <label class="mb-2 block font-medium">Pilih Ketua Komunitas</label>

                <select
                    name="leader_id"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                >
                    <option value="">-- Pilih Pengguna --</option>

                    @foreach($users as $user)
                        <option
                            value="{{ $user->id }}"
                            @selected((string) old('leader_id', session('created_user_id')) === (string) $user->id)
                        >
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>

                <p class="mt-2 text-sm text-gray-400">
                    Hanya pengguna dengan role Member yang dapat dipilih.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700"
                >
                    Simpan Komunitas
                </button>

                <a
                    href="{{ route('admin.communities.index') }}"
                    class="rounded-lg bg-gray-700 px-6 py-3 text-center font-semibold text-white hover:bg-gray-600"
                >
                    Batal
                </a>
            </div>
        </form>
    @endif

    <div class="mt-8 border-t border-gray-800 pt-6">
        <details class="rounded-xl border border-gray-700 bg-gray-950/40 p-4" @if($users->isEmpty()) open @endif>
            <summary class="cursor-pointer font-semibold text-blue-400 hover:text-blue-300">
                + Buat akun calon Ketua baru
            </summary>

            <p class="mt-3 text-sm text-gray-400">
                Akun baru otomatis ber-role Member dan belum tergabung ke komunitas mana pun.
                Setelah dibuat, akun langsung muncul di pilihan Ketua.
            </p>

            <form action="{{ route('admin.leader-accounts.store') }}" method="POST" class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="leader_name" class="mb-2 block font-medium">Nama lengkap</label>

                    <input
                        id="leader_name"
                        name="leader_name"
                        type="text"
                        value="{{ old('leader_name') }}"
                        autocomplete="name"
                        required
                        placeholder="Nama calon Ketua"
                        class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                    >

                    @error('leader_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="leader_email" class="mb-2 block font-medium">Email</label>

                    <input
                        id="leader_email"
                        name="leader_email"
                        type="email"
                        value="{{ old('leader_email') }}"
                        autocomplete="email"
                        required
                        placeholder="ketua@email.com"
                        class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                    >

                    @error('leader_email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="leader_phone" class="mb-2 block font-medium">Nomor telepon (opsional)</label>

                    <input
                        id="leader_phone"
                        name="leader_phone"
                        type="tel"
                        value="{{ old('leader_phone') }}"
                        autocomplete="tel"
                        placeholder="08xxxxxxxxxx"
                        class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                    >

                    @error('leader_phone')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="leader_password" class="mb-2 block font-medium">Password</label>

                    <input
                        id="leader_password"
                        name="leader_password"
                        type="password"
                        autocomplete="new-password"
                        minlength="12"
                        required
                        placeholder="Minimal 12 karakter"
                        class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                    >

                    <p class="mt-1 text-sm text-gray-400">
                        Minimal 12 karakter, kombinasi huruf besar/kecil, angka, dan simbol.
                    </p>

                    @error('leader_password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="leader_password_confirmation" class="mb-2 block font-medium">Konfirmasi password</label>

                    <input
                        id="leader_password_confirmation"
                        name="leader_password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        minlength="12"
                        required
                        placeholder="Ulangi password"
                        class="w-full rounded-lg border border-gray-700 bg-gray-950 p-3 text-white focus:border-blue-500 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700"
                >
                    Buat Akun Calon Ketua
                </button>
            </form>
        </details>
    </div>

    @if($users->isEmpty())
        <div class="mt-8">
            <a href="{{ route('admin.communities.index') }}"
               class="text-blue-400 hover:text-blue-300">
                ← Kembali ke Manajemen Komunitas
            </a>
        </div>
    @endif

</div>
@endsection
