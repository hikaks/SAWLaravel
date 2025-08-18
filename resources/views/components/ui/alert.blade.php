@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
    'icon' => null,
    'actions' => null,
])

@php
$types = [
    'success' => [
        'container' => 'bg-success-50 border-success-200 text-success-800',
        'icon' => 'fas fa-check-circle text-success-400',
        'title' => 'text-success-900',
        'default_icon' => 'fas fa-check-circle',
    ],
    'error' => [
        'container' => 'bg-danger-50 border-danger-200 text-danger-800',
        'icon' => 'fas fa-exclamation-circle text-danger-400',
        'title' => 'text-danger-900',
        'default_icon' => 'fas fa-exclamation-circle',
    ],
    'warning' => [
        'container' => 'bg-warning-50 border-warning-200 text-warning-800',
        'icon' => 'fas fa-exclamation-triangle text-warning-400',
        'title' => 'text-warning-900',
        'default_icon' => 'fas fa-exclamation-triangle',
    ],
    'info' => [
        'container' => 'bg-blue-50 border-blue-200 text-blue-800',
        'icon' => 'fas fa-info-circle text-blue-400',
        'title' => 'text-blue-900',
        'default_icon' => 'fas fa-info-circle',
    ],
];

$config = $types[$type] ?? $types['info'];
$iconClass = $icon ?? $config['default_icon'];
@endphp

<div class="rounded-lg border p-4 {{ $config['container'] }} {{ $dismissible ? 'pr-12' : '' }}" 
     role="alert" 
     {{ $attributes }}>
    <div class="flex items-start">
        {{-- Icon --}}
        <div class="flex-shrink-0">
            <i class="{{ $iconClass }} {{ $config['icon'] }} text-xl"></i>
        </div>
        
        {{-- Content --}}
        <div class="ml-3 flex-1">
            @if($title)
                <h3 class="text-sm font-semibold {{ $config['title'] }} mb-1">
                    {{ $title }}
                </h3>
            @endif
            
            <div class="text-sm">
                {{ $slot }}
            </div>
            
            {{-- Actions --}}
            @if($actions)
                <div class="mt-3 flex space-x-3">
                    {{ $actions }}
                </div>
            @endif
        </div>
        
        {{-- Dismiss Button --}}
        @if($dismissible)
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button type="button" 
                            class="inline-flex rounded-md p-1.5 hover:bg-opacity-20 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200"
                            onclick="this.closest('[role=alert]').remove()">
                        <span class="sr-only">{{ __('Dismiss') }}</span>
                        <i class="fas fa-times text-lg opacity-60 hover:opacity-80"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>