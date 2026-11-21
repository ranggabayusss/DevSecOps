@extends('layouts.app')

@section('title', $event->title)

@section('content')
@php
    $statusClasses = [
        'upcoming' => 'border-sky-500/30 bg-sky-500/10 text-sky-300',
        'ongoing' => 'border-amber-500/30 bg-amber-500/10 text-amber-300',
        'finished' => 'border-gray-700 bg-gray-800 text-gray-300',
    ];

    $rsvpClasses = [
        'attending' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'declined' => 'border-red-500/30 bg-red-500/10 text-red-300',
    ];

    $statusClass = $statusClasses[$event->status] ?? 'border-gray-700 bg-gray-800 text-gray-300';
@endphp

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        <a href="{{ route('events.index') }}" class="text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-orange-400">
            &larr; Semua acara
        </a>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                {{ $event->statusLabel() }}
            </span>

            @if ($myRsvp)
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $rsvpClasses[$myRsvp->status] ?? '' }}">
                    Konfirmasimu: {{ $myRsvp->statusLabel() }}
                </span>
            @endif
        </div>

        <h1 class="mt-3 text-2xl font-semibold tracking-tight text-white">{{ $event->title }}</h1>
    </div>

    @if (Auth::user()->canManageEvents())
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.export', $event) }}"
               class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                Download Excel
            </a>

            <a href="{{ route('events.edit', $event) }}"
               class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
                Edit
            </a>

            <form action="{{ route('events.destroy', $event) }}"
                  method="POST"
                  data-confirm="Yakin ingin menghapus acara ini?">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-300 transition hover:bg-red-500/20">
                    Hapus
                </button>
            </form>
        </div>
    @endif
</div>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    <section class="rounded-xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Detail Kegiatan</h2>

        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-gray-300">{{ $event->description }}</p>

        <dl class="mt-6 grid gap-4 border-t border-gray-800 pt-5 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Waktu</dt>
                <dd class="mt-1 font-medium text-gray-200">{{ $event->starts_at?->format('d M Y, H:i') }}</dd>
            </div>

            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Lokasi / titik kumpul</dt>
                <dd class="mt-1 font-medium text-gray-200">{{ $event->location }}</dd>
            </div>

            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Dibuat oleh</dt>
                <dd class="mt-1 text-gray-300">{{ $event->creator?->name ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs uppercase tracking-wider text-gray-500">Terakhir diperbarui</dt>
                <dd class="mt-1 text-gray-300">
                    {{ $event->updater?->name ?? '-' }}
                    @if ($event->updated_at)
                        <span class="text-gray-500">&middot; {{ $event->updated_at->format('d M Y, H:i') }}</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <aside class="h-fit rounded-xl border border-gray-800 bg-gray-900 p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Konfirmasi Kehadiran</h2>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 px-3.5 py-3">
                <p class="text-2xl font-semibold text-emerald-300">{{ $attendingCount }}</p>
                <p class="mt-0.5 text-xs text-emerald-400/80">Akan ikut</p>
            </div>

            <div class="rounded-lg border border-red-500/20 bg-red-500/5 px-3.5 py-3">
                <p class="text-2xl font-semibold text-red-300">{{ $declinedCount }}</p>
                <p class="mt-0.5 text-xs text-red-400/80">Tidak ikut</p>
            </div>
        </div>

        @if ($event->isFinished())
            <p class="mt-5 rounded-lg bg-gray-800/60 px-3.5 py-3 text-sm leading-relaxed text-gray-400">
                Acara sudah selesai. Konfirmasi kehadiran dikunci.
            </p>
        @elseif (! Auth::user()->hasCompleteMemberProfile())
            <div class="mt-5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3.5 py-3 text-sm leading-relaxed text-amber-200">
                Lengkapi nama motor dan plat nomor di profil sebelum melakukan RSVP.
                <a href="{{ route('profile.edit') }}" class="mt-2 block font-semibold text-amber-100 underline">
                    Lengkapi profil
                </a>
            </div>
        @else
            <div class="mt-5 space-y-2.5">
                <form action="{{ route('events.rsvp', $event) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="attending">

                    <button type="submit"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm font-semibold transition {{ $myRsvp?->status === 'attending' ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20' }}">
                        {{ $myRsvp?->status === 'attending' ? 'Terpilih: Akan Ikut' : 'Akan Ikut' }}
                    </button>
                </form>

                <form action="{{ route('events.rsvp', $event) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="declined">

                    <button type="submit"
                            class="w-full rounded-lg border px-4 py-2.5 text-sm font-semibold transition {{ $myRsvp?->status === 'declined' ? 'border-red-400 bg-red-500/20 text-red-200' : 'border-red-500/30 bg-red-500/10 text-red-300 hover:bg-red-500/20' }}">
                        {{ $myRsvp?->status === 'declined' ? 'Terpilih: Tidak Ikut' : 'Tidak Ikut' }}
                    </button>
                </form>
            </div>

            <p class="mt-4 text-xs leading-relaxed text-gray-500">
                Pilihan dapat diubah selama status acara belum selesai.
            </p>
        @endif
    </aside>
</div>

<section id="kehadiran" class="mt-8 scroll-mt-24">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-white">Daftar Kehadiran</h2>
            <p class="mt-1 text-sm text-gray-400">
                {{ $attendance->total() }} anggota sudah memberikan konfirmasi.
            </p>
        </div>

        @if (Auth::user()->canManageEvents())
            <a href="{{ route('events.export', $event) }}"
               class="text-sm font-semibold text-emerald-400 hover:text-emerald-300">
                Download Excel &darr;
            </a>
        @endif
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-800 bg-gray-900">
        <table class="w-full text-left">
            <thead class="border-b border-gray-800 bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 font-medium">No.</th>
                    <th class="px-4 py-3 font-medium">Nomor Anggota</th>
                    <th class="px-4 py-3 font-medium">Nama Lengkap</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Nama Motor</th>
                    <th class="px-4 py-3 font-medium">Plat Nomor</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Waktu Konfirmasi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-800">
                @forelse ($attendance as $index => $rsvp)
                    <tr class="hover:bg-gray-800/40">
                        <td class="px-4 py-3.5 text-sm text-gray-500">
                            {{ ($attendance->firstItem() ?? 1) + $index }}
                        </td>

                        <td class="px-4 py-3.5 text-sm font-medium text-orange-300">
                            {{ $rsvp->user?->member_number ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm font-medium text-white">
                            {{ $rsvp->user?->name ?? 'Anggota tidak tersedia' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $rsvp->user ? ucfirst($rsvp->user->role) : '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $rsvp->user?->motorcycle_type ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $rsvp->user?->license_plate ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $rsvpClasses[$rsvp->status] ?? 'border-gray-700 bg-gray-800 text-gray-300' }}">
                                {{ $rsvp->statusLabel() }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 text-sm text-gray-400">
                            {{ $rsvp->confirmed_at?->format('d M Y, H:i') ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">
                            Belum ada anggota yang melakukan konfirmasi kehadiran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $attendance->links() }}</div>
</section>
@endsection
