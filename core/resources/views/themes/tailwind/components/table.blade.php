{{-- shadcn's Table on a plain <table> (thead, tbody, th, td) put inside it. --}}
<div {{ $attributes->merge(['class' => 'relative w-full overflow-x-auto']) }}>
    <div class="text-sm [&_table]:w-full [&_table]:caption-bottom [&_thead_tr]:border-b [&_tbody_tr]:hover:bg-muted/50 [&_tbody_tr]:border-b [&_tbody_tr:last-child]:border-0 [&_th]:text-foreground [&_th]:h-10 [&_th]:px-4 [&_th]:text-left [&_th]:align-middle [&_th]:font-medium [&_th]:whitespace-nowrap [&_td]:px-4 [&_td]:py-2 [&_td]:align-middle [&_a]:font-medium [&_a]:underline-offset-4 [&_a:hover]:underline [&_.text-end]:text-right">
        {{ $slot }}
    </div>
</div>
