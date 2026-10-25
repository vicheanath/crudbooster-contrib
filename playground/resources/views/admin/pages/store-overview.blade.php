<div class="row g-3 mb-3">
    @foreach($stats as $stat)
        <div class="col-md-4">
            <a href="{{ $stat['url'] }}" class="text-decoration-none">
                <div class="small-box text-bg-{{ $stat['color'] }}">
                    <div class="inner">
                        <h3>{{ $stat['value'] }}</h3>
                        <p>{{ $stat['label'] }}</p>
                    </div>
                    <i class="small-box-icon {{ $stat['icon'] }}"></i>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Low stock</h3></div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Product</th><th class="text-end">Stock</th></tr></thead>
            <tbody>
            @forelse($lowStock as $product)
                <tr>
                    <td><a href="{{ \App\Admin\Resources\ProductResource::url('edit', $product) }}">{{ $product->name }}</a></td>
                    <td class="text-end">{{ $product->stock }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-body-secondary">Everything is in stock.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
