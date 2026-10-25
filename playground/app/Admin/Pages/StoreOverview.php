<?php

namespace App\Admin\Pages;

use App\Admin\Resources\ProductResource;
use App\Admin\Resources\ReviewResource;
use App\Models\ShopProduct;
use App\Models\ShopReview;
use CrudBooster\Pages\Page;

class StoreOverview extends Page
{
    protected static ?string $navigationIcon = 'fa fa-pie-chart';

    protected static ?string $navigationGroup = 'Catalog (resources)';

    protected static int $navigationSort = -1;

    protected static string $view = 'admin.pages.store-overview';

    public function data(): array
    {
        return [
            'stats' => [
                ['label' => 'Products', 'value' => ShopProduct::query()->count(), 'icon' => 'fa fa-cube', 'color' => 'primary', 'url' => ProductResource::url()],
                ['label' => 'Out of stock', 'value' => ShopProduct::query()->where('stock', 0)->count(), 'icon' => 'fa fa-exclamation-triangle', 'color' => 'danger', 'url' => ProductResource::url(parameters: ['filters' => ['out_of_stock' => 1]])],
                ['label' => 'Reviews to moderate', 'value' => ShopReview::query()->where('is_published', false)->count(), 'icon' => 'fa fa-star-half-o', 'color' => 'warning', 'url' => ReviewResource::url(parameters: ['filters' => ['is_published' => 0]])],
            ],
            'lowStock' => ShopProduct::query()->where('stock', '<', 10)->orderBy('stock')->limit(5)->get(),
        ];
    }
}
