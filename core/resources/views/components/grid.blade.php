@props(['cols' => 3])
@php
    $columns = [
        1 => 'row-cols-1',
        2 => 'row-cols-1 row-cols-sm-2',
        3 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
        4 => 'row-cols-1 row-cols-sm-2 row-cols-lg-4',
    ][(int) $cols] ?? 'row-cols-1 row-cols-sm-2 row-cols-lg-3';
@endphp
<div {{ $attributes->merge(['class' => 'row g-3 mb-3 '.$columns]) }}>{{ $slot }}</div>
