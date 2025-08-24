@props([
    'variant' => 'primary',
    'size' => 'md',
    'rounded' => true,
    'dot' => false,
])

@php
$baseClasses = 'inline-flex items-center font-medium';

$variants = [
    'primary' => 'bg-primary-100 text-primary-800 border border-primary-200',
    'secondary' => 'bg-gray-100 text-gray-800 border border-gray-200',
    'success' => 'bg-success-100 text-success-800 border border-success-200',
    'warning' => 'bg-warning-100 text-warning-800 border border-warning-200',
    'danger' => 'bg-danger-100 text-danger-800 border border-danger-200',
    'info' => 'bg-blue-100 text-blue-800 border border-blue-200',
];

$sizes = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-3.5 py-1.5 text-sm',
];

$roundedClasses = $rounded ? 'rounded-full' : 'rounded-md';

$classes = implode(' ', [
    $baseClasses,
    $variants[$variant] ?? $variants['primary'],
    $sizes[$size] ?? $sizes['md'],
    $roundedClasses
]);
@endphp

<span class="{{ $classes }}" {{ $attributes }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-current opacity-75"></span>
    @endif
    {{ $slot }}
</span>