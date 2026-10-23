{{-- The current admin's initials in place of a photo. $class picks the AdminLTE slot (user-image, img-circle). --}}
@php
    $cbName = trim((string) CRUDBooster::myName());
    $cbWords = preg_split('/\s+/u', $cbName, -1, PREG_SPLIT_NO_EMPTY);
    $cbInitials = mb_strtoupper(implode('', array_map(fn ($word) => mb_substr($word, 0, 1), array_slice($cbWords, 0, 2)))) ?: '?';
@endphp
<span class="{{ $class }} cb-initials" role="img" aria-label="{{ $cbName }}">{{ $cbInitials }}</span>
