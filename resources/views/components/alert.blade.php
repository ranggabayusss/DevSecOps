@props(['type' => 'info'])

@php
    $styles = match ($type) {
        'success' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'error' => 'border-red-500/30 bg-red-500/10 text-red-300',
        'warning' => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
        default => 'border-gray-800 bg-gray-900 text-gray-300',
    };
@endphp

<div {{ $attributes->merge(['class' => 'mb-6 rounded-lg border px-4 py-3 text-sm ' . $styles]) }} role="status">
    {{ $slot }}
</div>
