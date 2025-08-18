@props([
    'status',
    'size' => 'md',
    'showIcon' => true,
])

@php
$statusConfig = [
    'active' => [
        'variant' => 'success',
        'icon' => 'fas fa-check-circle',
        'text' => __('Active')
    ],
    'inactive' => [
        'variant' => 'secondary',
        'icon' => 'fas fa-pause-circle',
        'text' => __('Inactive')
    ],
    'pending' => [
        'variant' => 'warning',
        'icon' => 'fas fa-clock',
        'text' => __('Pending')
    ],
    'completed' => [
        'variant' => 'success',
        'icon' => 'fas fa-check-circle',
        'text' => __('Completed')
    ],
    'failed' => [
        'variant' => 'danger',
        'icon' => 'fas fa-times-circle',
        'text' => __('Failed')
    ],
    'processing' => [
        'variant' => 'info',
        'icon' => 'fas fa-spinner',
        'text' => __('Processing')
    ],
    'draft' => [
        'variant' => 'secondary',
        'icon' => 'fas fa-edit',
        'text' => __('Draft')
    ],
    'published' => [
        'variant' => 'primary',
        'icon' => 'fas fa-eye',
        'text' => __('Published')
    ],
];

$config = $statusConfig[strtolower($status)] ?? $statusConfig['pending'];
$iconClass = $status === 'processing' ? $config['icon'] . ' animate-spin' : $config['icon'];
@endphp

<x-ui.badge 
    :variant="$config['variant']" 
    :size="$size"
    {{ $attributes }}>
    @if($showIcon)
        <i class="{{ $iconClass }} {{ $slot->isNotEmpty() ? 'mr-1.5' : '' }} text-current"></i>
    @endif
    {{ $slot->isNotEmpty() ? $slot : $config['text'] }}
</x-ui.badge>