{{-- Built from <x-cb::...> components only, so it looks right in both the AdminLTE and the Tailwind theme. --}}
<x-cb::grid cols="3">
    @foreach($stats as $stat)
        <x-cb::stat :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" :url="$stat['url']" />
    @endforeach
</x-cb::grid>

<x-cb::grid cols="2">
<x-cb::card title="Latest orders" flush>
    <x-slot:actions><x-cb::button :href="\App\Admin\Resources\Shop\OrderResource::url()" size="sm">All orders</x-cb::button></x-slot:actions>
    <x-cb::table>
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="text-end">Total</th></tr></thead>
            <tbody>
            @forelse($latestOrders as $order)
                <tr>
                    <td><a href="{{ \App\Admin\Resources\Shop\OrderResource::url('view', $order) }}">{{ $order->order_number }}</a></td>
                    <td>{{ $order->customer?->name }}</td>
                    <td><x-cb::badge :color="\App\Admin\Resources\Shop\OrderResource::COLORS[$order->status] ?? 'secondary'">{{ \App\Models\ShopOrder::STATUSES[$order->status] ?? $order->status }}</x-cb::badge></td>
                    <td class="text-end">${{ number_format((float) $order->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-cb::table>
</x-cb::card>

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
</x-cb::grid>
