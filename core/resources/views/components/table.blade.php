{{-- Styles a plain <table> put inside it. --}}
<div {{ $attributes->merge(['class' => 'table-responsive']) }}>
    <div class="cb-plain-table">{{ $slot }}</div>
</div>
@once
    <style>.cb-plain-table > table { width: 100%; margin-bottom: 0; } .cb-plain-table > table > :not(caption) > * > * { padding: .75rem 1rem; border-bottom: 1px solid var(--bs-border-color); } .cb-plain-table th { font-weight: 600; }</style>
@endonce
