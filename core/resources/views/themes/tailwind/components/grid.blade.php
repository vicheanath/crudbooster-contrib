@props(['cols' => 3])
@php
    $columns = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    ][(int) $cols] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
@endphp
<div {{ $attributes->merge(['class' => 'mb-6 grid gap-4 '.$columns]) }}>{{ $slot }}</div>
