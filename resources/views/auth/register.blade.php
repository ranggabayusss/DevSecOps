@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
<div class="rounded-xl border border-gray-800 bg-gray-900 p-6 shadow-xl sm:p-8">
    <h1 class="text-xl font-semibold tracking-tight text-white">Buat akun</h1>
    <p class="mt-1.5 text-sm text-gray-400">
        Bergabung dengan komunitas Unknown Rider.
    </p>

    @if ($errors->any())
        <x-alert type="error" class="mt-5">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form action="{{ route('register.store') }}" method="POST" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-300">Nama lengkap</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                autocomplete="name"
                autofocus
                required
                placeholder="Nama kamu"
                class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
            >
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-300">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                placeholder="nama@email.com"
                class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
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
                value="{{ old('phone') }}"
                autocomplete="tel"
                placeholder="08xxxxxxxxxx"
                class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
            >
        </div>

        <x-password-field
            id="password"
            name="password"
            label="Password"
            autocomplete="new-password"
            minlength="8"
            placeholder="Minimal 8 karakter"
        >
            <p class="mt-1.5 text-xs text-gray-500">
                Minimal 8 karakter, gunakan huruf besar, huruf kecil, angka, dan simbol.
            </p>
        </x-password-field>

        <x-password-field
            id="password_confirmation"
            name="password_confirmation"
            label="Konfirmasi password"
            autocomplete="new-password"
            minlength="8"
            placeholder="Ulangi password"
        />

        <button type="submit"
                class="w-full rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/60">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-medium text-orange-400 hover:text-orange-300">
            Masuk
        </a>
    </p>
</div>
@endsection
