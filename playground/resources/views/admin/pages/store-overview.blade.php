{{-- Built from <x-cb::...> components only, so it looks right in both the AdminLTE and the Tailwind theme. --}}
<x-cb::grid cols="3">
    @foreach($stats as $stat)
        <x-cb::stat :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" :url="$stat['url']" />
    @endforeach
</x-cb::grid>

<x-cb::card title="Low stock" description="Products with fewer than 10 units." flush>
    <x-cb::table>
        <table>
            <thead><tr><th>Product</th><th class="text-end">Stock</th></tr></thead>
            <tbody>
            @forelse($lowStock as $product)
                <tr>
                    <td><a href="{{ \App\Admin\Resources\ProductResource::url('edit', $product) }}">{{ $product->name }}</a></td>
                    <td class="text-end"><x-cb::badge :color="$product->stock ? 'warning' : 'danger'">{{ $product->stock }}</x-cb::badge></td>
                </tr>
            @empty
                <tr><td colspan="2">Everything is in stock.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-cb::table>
</x-cb::card>
