@props([
    'responsive' => true,
    'striped' => false,
    'hover' => true,
    'bordered' => false,
    'size' => 'md',
])

@php
$tableClasses = collect(['table']);

if ($striped) $tableClasses->push('table-striped');
if ($hover) $tableClasses->push('table-hover');
if ($bordered) $tableClasses->push('table-bordered');

$sizeClasses = [
    'sm' => 'table-sm',
    'md' => '',
    'lg' => 'table-lg',
];

if (isset($sizeClasses[$size])) {
    $tableClasses->push($sizeClasses[$size]);
}

$tableClass = $tableClasses->filter()->implode(' ');
@endphp

<div class="{{ $responsive ? 'table-responsive' : '' }}" {{ $attributes }}>
    <table class="{{ $tableClass }} align-middle">
        {{ $slot }}
    </table>
</div>