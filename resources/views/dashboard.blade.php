<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Unknown Rider</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 text-white">
    <header class="border-b border-orange-500 bg-black px-6 py-5">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <h1 class="text-2xl font-black tracking-wide text-orange-500">UNKNOWN RIDER</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-300">Halo, <strong class="text-white">{{ Auth::user()->name }}</strong></span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold hover:bg-red-700">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
        <section class="mb-8 rounded-2xl border border-gray-800 bg-gray-900 p-7">
            <p class="text-sm font-semibold uppercase tracking-wider text-orange-400">Dashboard</p>
            <h2 class="mt-2 text-3xl font-bold">Selamat datang, {{ Auth::user()->name }}.</h2>
            <p class="mt-3 max-w-2xl text-gray-400">
                Kelola informasi komunitas dan aktivitas keanggotaan Unknown Rider dari satu tempat.
            </p>
        </section>

        @if (Auth::user()->role === 'ketua' && $ledCommunity)
            <section class="mb-8 rounded-2xl border border-orange-500/40 bg-gray-900 p-6">
                <h3 class="text-xl font-bold text-orange-400">Manajemen Ketua</h3>
                <p class="mt-2 text-gray-300">Komunitas yang kamu kelola: <strong>{{ $ledCommunity->name }}</strong></p>
                <p class="mt-2 text-sm text-gray-400">Tinjau dan proses permintaan anggota yang ingin bergabung.</p>
                <a href="{{ route('ketua.communities.requests.index', $ledCommunity->id) }}"
                   class="mt-5 inline-block rounded-lg bg-orange-600 px-5 py-3 font-semibold text-white hover:bg-orange-700">
                    Lihat Permintaan Bergabung
                </a>
            </section>
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <a href="{{ route('communities.index') }}" class="rounded-xl border border-gray-800 bg-gray-900 p-6 transition hover:border-orange-500">
                <h3 class="text-lg font-bold text-orange-400">Daftar Komunitas</h3>
                <p class="mt-2 text-sm text-gray-400">Lihat komunitas yang tersedia dan status keanggotaanmu.</p>
            </a>
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-800 bg-gray-900 p-6 transition hover:border-orange-500">
                    <h3 class="text-lg font-bold text-orange-400">Dashboard Admin</h3>
                    <p class="mt-2 text-sm text-gray-400">Buka pengelolaan komunitas dan anggota.</p>
                </a>
            @else
                <div class="rounded-xl border border-gray-800 bg-gray-900 p-6">
                    <h3 class="text-lg font-bold text-orange-400">Akun Saya</h3>
                    <p class="mt-2 text-sm text-gray-400">Role akun: {{ ucfirst(Auth::user()->role) }}</p>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
