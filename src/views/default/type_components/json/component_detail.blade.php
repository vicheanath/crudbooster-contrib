@php
    // Read-only, so the JSON is printed as formatted text rather than loaded into an editor.
    $jsonValue = json_decode((string) $value, true);
@endphp
@if($value !== null && $value !== '')
<pre style="margin:0;white-space:pre-wrap">{{ $jsonValue === null ? $value : json_encode($jsonValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
@endif
