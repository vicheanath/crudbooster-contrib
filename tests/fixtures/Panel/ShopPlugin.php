<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use CrudBooster\Navigation\NavigationItem;
use CrudBooster\Panel\Panel;
use CrudBooster\Plugins\Plugin;

class ShopPlugin implements Plugin
{
    public bool $booted = false;

    public function getId(): string
    {
        return 'shop';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ProductResource::class, CategoryResource::class])
            ->navigationItems([NavigationItem::make('Storefront')->url('https://shop.test', openInNewTab: true)->icon('fa fa-shopping-bag')->group('Shop')->sort(99)]);
    }

    public function boot(Panel $panel): void
    {
        $this->booted = true;
        $panel->renderHook('index.before', fn ($resource) => '<div id="shop-hook">'.e($resource::getPluralLabel()).'</div>');
    }
}
