<?php

namespace App\Admin\Pages;

use App\Admin\Resources\ProductResource;
use App\Admin\Resources\ReviewResource;
use App\Admin\Resources\Shop\OrderResource;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Models\ShopReview;
use CrudBooster\Pages\Page;

class StoreOverview extends Page
{
    protected static ?string $navigationIcon = 'fa fa-pie-chart';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = -1;

    protected static string $view = 'admin.pages.store-overview';

    public function data(): array
    {
        return [
            'stats' => [
                ['label' => 'Products', 'value' => ShopProduct::query()->count(), 'icon' => 'fa fa-cube', 'color' => 'primary', 'url' => ProductResource::url()],
                ['label' => 'Out of stock', 'value' => ShopProduct::query()->where('stock', 0)->count(), 'icon' => 'fa fa-exclamation-triangle', 'color' => 'danger', 'url' => ProductResource::url(parameters: ['filters' => ['out_of_stock' => 1]])],
                ['label' => 'Orders to ship', 'value' => ShopOrder::query()->where('status', 'paid')->count(), 'icon' => 'fa fa-truck', 'color' => 'info', 'url' => OrderResource::url(parameters: ['filters' => ['status' => 'paid']])],
                ['label' => 'Revenue this month', 'value' => '$'.number_format((float) ShopOrder::query()->where('status', '!=', 'cancelled')->where('placed_at', '>=', now()->startOfMonth())->sum('total'), 2), 'icon' => 'fa fa-money', 'color' => 'success', 'url' => OrderResource::url(parameters: ['filters' => ['this_month' => 1]])],
                ['label' => 'Reviews to moderate', 'value' => ShopReview::query()->where('is_published', false)->count(), 'icon' => 'fa fa-star-half-o', 'color' => 'warning', 'url' => ReviewResource::url(parameters: ['filters' => ['is_published' => 0]])],
            ],
            'latestOrders' => ShopOrder::query()->with('customer')->latest('placed_at')->limit(5)->get(),
            'lowStock' => ShopProduct::query()->where('stock', '<', 10)->orderBy('stock')->limit(5)->get(),
        ];
    }
}
