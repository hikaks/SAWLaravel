@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'external' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-primary-600 hover:bg-primary-700 text-white border border-primary-600 hover:border-primary-700 focus:ring-primary-500',
    'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-900 border border-gray-300 hover:border-gray-400 focus:ring-gray-500',
    'success' => 'bg-success-600 hover:bg-success-700 text-white border border-success-600 hover:border-success-700 focus:ring-success-500',
    'warning' => 'bg-warning-500 hover:bg-warning-600 text-white border border-warning-500 hover:border-warning-600 focus:ring-warning-500',
    'danger' => 'bg-danger-600 hover:bg-danger-700 text-white border border-danger-600 hover:border-danger-700 focus:ring-danger-500',
    'info' => 'bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 hover:border-blue-700 focus:ring-blue-500',
    'outline-primary' => 'bg-transparent hover:bg-primary-50 text-primary-700 border border-primary-300 hover:border-primary-400 focus:ring-primary-500',
    'outline-secondary' => 'bg-transparent hover:bg-gray-50 text-gray-700 border border-gray-300 hover:border-gray-400 focus:ring-gray-500',
    'outline-success' => 'bg-transparent hover:bg-success-50 text-success-700 border border-success-300 hover:border-success-400 focus:ring-success-500',
    'outline-warning' => 'bg-transparent hover:bg-warning-50 text-warning-700 border border-warning-300 hover:border-warning-400 focus:ring-warning-500',
    'outline-danger' => 'bg-transparent hover:bg-danger-50 text-danger-700 border border-danger-300 hover:border-danger-400 focus:ring-danger-500',
    'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 border-0 focus:ring-gray-500',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-5 py-3 text-base',
    'xl' => 'px-6 py-3.5 text-lg',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);

$isDisabled = $disabled || $loading;
$tag = $href ? 'a' : 'button';
@endphp

@if($href)
    <a href="{{ $href }}" 
       {{ $external ? 'target="_blank" rel="noopener noreferrer"' : '' }}
       class="{{ $classes }}"
       @if($isDisabled) aria-disabled="true" @endif
       {{ $attributes }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('Loading...') }}
        @else
            @if($icon && $iconPosition === 'left')
                <i class="{{ $icon }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }} text-current"></i>
            @endif
            {{ $slot }}
            @if($icon && $iconPosition === 'right')
                <i class="{{ $icon }} {{ $slot->isNotEmpty() ? 'ml-2' : '' }} text-current"></i>
            @endif
        @endif
    </a>
@else
    <button type="{{ $type }}" 
            class="{{ $classes }}"
            @if($isDisabled) disabled @endif
            {{ $attributes }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ __('Loading...') }}
        @else
            @if($icon && $iconPosition === 'left')
                <i class="{{ $icon }} {{ $slot->isNotEmpty() ? 'mr-2' : '' }} text-current"></i>
            @endif
            {{ $slot }}
            @if($icon && $iconPosition === 'right')
                <i class="{{ $icon }} {{ $slot->isNotEmpty() ? 'ml-2' : '' }} text-current"></i>
            @endif
        @endif
    </button>
@endif