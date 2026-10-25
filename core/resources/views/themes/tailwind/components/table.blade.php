{{-- Styles a plain <table> (thead, tbody, th, td) put inside it. --}}
<div {{ $attributes->merge(['class' => 'relative overflow-x-auto']) }}>
    <div class="min-w-full text-sm [&_table]:min-w-full [&_table]:divide-y [&_table]:divide-gray-200 dark:[&_table]:divide-white/10 [&_thead]:bg-gray-50 dark:[&_thead]:bg-white/5 [&_th]:px-5 [&_th]:py-3 [&_th]:text-left [&_th]:text-xs [&_th]:font-semibold [&_th]:tracking-wide [&_th]:text-gray-500 [&_th]:uppercase dark:[&_th]:text-gray-400 [&_tbody]:divide-y [&_tbody]:divide-gray-100 dark:[&_tbody]:divide-white/5 [&_td]:px-5 [&_td]:py-3 [&_td]:text-gray-700 dark:[&_td]:text-gray-300 [&_a]:font-medium [&_a]:text-brand-600 [&_a:hover]:underline dark:[&_a]:text-brand-400 [&_.text-end]:text-right">
        {{ $slot }}
    </div>
</div>
