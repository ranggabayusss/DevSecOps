@extends('layouts.app')

@section('title', 'Acara & Pengumuman')

@section('content')
<x-page-header
    title="Acara & Pengumuman"
    description="Lihat jadwal kegiatan komunitas dan konfirmasi kehadiranmu."
>
    @if (Auth::user()->canManageEvents())
        <x-slot:actions>
            <a href="{{ route('events.create') }}"
               class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                + Buat Acara
            </a>
        </x-slot:actions>
    @endif
</x-page-header>

@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if (session('error'))
    <x-alert type="error">{{ session('error') }}</x-alert>
@endif

@if ($events->isEmpty())
    <x-empty-state
        title="Belum ada acara"
        description="Pengumuman dan jadwal kegiatan komunitas akan tampil di sini."
    >
        @if (Auth::user()->canManageEvents())
            <a href="{{ route('events.create') }}"
               class="mt-5 inline-block rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                Buat Acara Pertama
            </a>
        @endif
    </x-empty-state>
@else
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
    @endphp

    <div class="space-y-4">
        @foreach ($events as $event)
            @php
                $myRsvp = $myRsvps->get($event->id);
                $statusClass = $statusClasses[$event->status] ?? 'border-gray-700 bg-gray-800 text-gray-300';
            @endphp

            <article class="rounded-xl border border-gray-800 bg-gray-900 p-5 transition hover:border-orange-500/40 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                {{ $event->statusLabel() }}
                            </span>

                            @if ($myRsvp)
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $rsvpClasses[$myRsvp->status] ?? '' }}">
                                    Konfirmasi: {{ $myRsvp->statusLabel() }}
                                </span>
                            @endif
                        </div>

                        <h2 class="mt-3 text-lg font-semibold text-white">
                            <a href="{{ route('events.show', $event) }}" class="hover:text-orange-400">
                                {{ $event->title }}
                            </a>
                        </h2>

                        <p class="mt-1.5 line-clamp-3 whitespace-pre-line text-sm leading-relaxed text-gray-400">{{ $event->description }}</p>

                        <dl class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <dt>Waktu</dt>
                                <dd class="text-gray-300">{{ $event->starts_at?->format('d M Y, H:i') }}</dd>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <dt>Lokasi</dt>
                                <dd class="text-gray-300">{{ $event->location }}</dd>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <dt>Hadir</dt>
                                <dd class="text-emerald-300">{{ $event->attending_rsvps_count }} anggota</dd>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <dt>RSVP</dt>
                                <dd class="text-gray-300">{{ $event->rsvps_count }} konfirmasi</dd>
                            </div>
                        </dl>
                    </div>

                    <a href="{{ route('events.show', $event) }}"
                       class="shrink-0 rounded-lg border border-gray-700 px-3.5 py-2 text-center text-sm font-semibold text-gray-200 transition hover:border-orange-500/50 hover:text-orange-300">
                        Detail &amp; RSVP
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">{{ $events->links() }}</div>
@endif
@endsection
