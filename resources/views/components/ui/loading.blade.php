@props([
    'type' => 'spinner',
    'size' => 'md',
    'color' => 'primary',
    'text' => null,
    'overlay' => false,
])

@php
$sizes = [
    'xs' => 'w-3 h-3',
    'sm' => 'w-4 h-4',
    'md' => 'w-6 h-6',
    'lg' => 'w-8 h-8',
    'xl' => 'w-12 h-12',
];

$colors = [
    'primary' => 'text-primary-600',
    'secondary' => 'text-gray-600',
    'success' => 'text-success-600',
    'warning' => 'text-warning-600',
    'danger' => 'text-danger-600',
    'white' => 'text-white',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
$colorClass = $colors[$color] ?? $colors['primary'];
@endphp

@if($overlay)
<div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" {{ $attributes }}>
    <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4 shadow-xl">
        @include('components.ui.loading-spinner')
        @if($text)
            <p class="text-gray-700 text-sm font-medium">{{ $text }}</p>
        @endif
    </div>
</div>
@else
<div class="flex items-center justify-center space-x-2" {{ $attributes }}>
    @if($type === 'spinner')
        @include('components.ui.loading-spinner')
    @elseif($type === 'dots')
        @include('components.ui.loading-dots')
    @elseif($type === 'pulse')
        @include('components.ui.loading-pulse')
    @endif
    
    @if($text)
        <span class="text-sm font-medium {{ $colorClass }}">{{ $text }}</span>
    @endif
</div>
@endif

{{-- Spinner Component --}}
@once
@push('loading-components')
<template id="loading-spinner-template">
    <svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
</template>
@endpush
@endonce

{{-- Inline Spinner --}}
<svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>