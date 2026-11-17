@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-dashed border-gray-800 bg-gray-900/50 px-6 py-12 text-center']) }}>
    <h3 class="text-sm font-semibold text-gray-200">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
