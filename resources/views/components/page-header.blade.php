@props(['title', 'description' => null])

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-white">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1.5 max-w-2xl text-sm text-gray-400">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>

    @if (isset($actions) && ! $actions->isEmpty())
        <div class="flex flex-wrap gap-2">{{ $actions }}</div>
    @endif
</div>
