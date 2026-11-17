@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="rounded-xl border border-gray-800 bg-gray-900 p-6 shadow-xl sm:p-8">
    <h1 class="text-xl font-semibold tracking-tight text-white">Masuk ke akun</h1>
    <p class="mt-1.5 text-sm text-gray-400">
        Lanjutkan ke dashboard Unknown Rider.
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

    <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-300">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="email"
                autofocus
                required
                placeholder="nama@email.com"
                class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
            >
        </div>

        <x-password-field
            id="password"
            name="password"
            label="Password"
            autocomplete="current-password"
            placeholder="Masukkan password"
        />

        <label class="flex items-center gap-2 text-sm text-gray-400">
            <input
                type="checkbox"
                name="remember"
                value="1"
                class="h-4 w-4 rounded border-gray-700 bg-gray-950 text-orange-500 focus:ring-orange-500/40"
            >
            Ingat saya
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/60">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-medium text-orange-400 hover:text-orange-300">
            Daftar sekarang
        </a>
    </p>
</div>
@endsection
