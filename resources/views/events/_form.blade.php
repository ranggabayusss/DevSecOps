@php
    $event = $event ?? null;
    $statusLabels = \App\Models\Event::statusLabels();
    $selectedStatus = old('status', $event->status ?? \App\Models\Event::STATUS_UPCOMING);
    $startsAtValue = old('starts_at', $event?->starts_at?->format('Y-m-d\TH:i'));
@endphp

@if ($errors->any())
    <x-alert type="error">
        <ul class="space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<div class="max-w-3xl">
    <div class="rounded-xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
        <form action="{{ $action }}" method="POST" class="space-y-5">
            @csrf

            @if ($method !== 'POST')
                @method($method)
            @endif

            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-gray-300">Judul acara / pengumuman</label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title', $event->title ?? '') }}"
                    required
                    maxlength="150"
                    placeholder="Contoh: Touring Sunmori Lembang"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-gray-300">Deskripsi / detail kegiatan</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    required
                    maxlength="5000"
                    placeholder="Tuliskan rute, agenda, perlengkapan, dan informasi lain."
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >{{ old('description', $event->description ?? '') }}</textarea>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="mb-1.5 block text-sm font-medium text-gray-300">Tanggal &amp; jam</label>
                    <input
                        id="starts_at"
                        name="starts_at"
                        type="datetime-local"
                        value="{{ $startsAtValue }}"
                        required
                        class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                    >
                </div>

                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-300">Status acara</label>
                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                    >
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="location" class="mb-1.5 block text-sm font-medium text-gray-300">Lokasi / titik kumpul</label>
                <input
                    id="location"
                    name="location"
                    type="text"
                    value="{{ old('location', $event->location ?? '') }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: Tugu Lembang, Bandung Barat"
                    class="w-full rounded-lg border border-gray-800 bg-gray-950 px-3.5 py-2.5 text-sm text-gray-100 placeholder:text-gray-600 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                >
            </div>

            <div class="flex flex-col gap-2 pt-1 sm:flex-row">
                <button type="submit"
                        class="rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-500">
                    {{ $submitLabel }}
                </button>

                <a href="{{ route('events.index') }}"
                   class="rounded-lg border border-gray-700 bg-gray-800/60 px-4 py-2.5 text-center text-sm font-semibold text-gray-200 transition hover:bg-gray-800">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
