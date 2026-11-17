<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Unknown Rider</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 text-gray-100 antialiased">

    @php
        $user = Auth::user();
        $ledCommunity = $user->role === 'ketua'
            ? \App\Models\Community::where('leader_id', $user->id)->first()
            : null;
        $navLink = 'rounded-lg px-3 py-1.5 font-medium transition';
    @endphp

    <header class="sticky top-0 z-20 border-b border-gray-800/80 bg-gray-950/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:gap-6 sm:px-6">

            <a href="{{ route('dashboard') }}"
               class="shrink-0 text-sm font-bold uppercase tracking-[0.22em] text-orange-500">
                Unknown Rider
            </a>

            <nav class="flex flex-1 items-center gap-1 overflow-x-auto text-sm" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}"
                   @class([$navLink, 'bg-gray-800 text-white' => request()->routeIs('dashboard'), 'text-gray-400 hover:text-white' => ! request()->routeIs('dashboard')])>
                    Dashboard
                </a>

                <a href="{{ route('communities.index') }}"
                   @class([$navLink, 'bg-gray-800 text-white' => request()->routeIs('communities.*'), 'text-gray-400 hover:text-white' => ! request()->routeIs('communities.*')])>
                    Komunitas
                </a>

                @if ($ledCommunity)
                    <a href="{{ route('ketua.communities.requests.index', $ledCommunity->id) }}"
                       @class([$navLink, 'bg-gray-800 text-white' => request()->routeIs('ketua.*'), 'text-gray-400 hover:text-white' => ! request()->routeIs('ketua.*')])>
                        Permintaan
                    </a>
                @endif

                @if ($user->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}"
                       @class([$navLink, 'bg-gray-800 text-white' => request()->routeIs('admin.*'), 'text-gray-400 hover:text-white' => ! request()->routeIs('admin.*')])>
                        Admin
                    </a>
                @endif
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                <div class="hidden text-right leading-tight sm:block">
                    <p class="text-sm font-medium text-gray-200">{{ $user->name }}</p>
                    <p class="text-[11px] uppercase tracking-wider text-gray-500">{{ $user->role }}</p>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="rounded-lg border border-gray-800 px-3 py-1.5 text-sm font-medium text-gray-400 transition hover:border-gray-700 hover:text-gray-200">
                        Keluar
                    </button>
                </form>
            </div>

        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-6xl px-4 pb-8 text-xs text-gray-600 sm:px-6">
        &copy; {{ date('Y') }} Unknown Rider
    </footer>

</body>
</html>
