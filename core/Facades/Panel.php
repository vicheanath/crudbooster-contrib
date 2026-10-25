<?php

namespace CrudBooster\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \CrudBooster\Panel\Panel resources(array $resources)
 * @method static \CrudBooster\Panel\Panel resource(string $resource)
 * @method static \CrudBooster\Panel\Panel pages(array $pages)
 * @method static \CrudBooster\Panel\Panel page(string $page)
 * @method static \CrudBooster\Panel\Panel discover(string $directory, string $namespace)
 * @method static \CrudBooster\Panel\Panel plugin(\CrudBooster\Plugins\Plugin $plugin)
 * @method static \CrudBooster\Panel\Panel navigationItems(array $items)
 * @method static \CrudBooster\Panel\Panel navigationGroups(array $groups)
 * @method static \CrudBooster\Panel\Panel renderHook(string $name, \Closure|string $content)
 * @method static array getResources()
 * @method static array getPages()
 * @method static string|null getResource(string $slug)
 * @method static string|null getResourceForModel(string $model)
 *
 * @see \CrudBooster\Panel\Panel
 */
class Panel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CrudBooster\Panel\Panel::class;
    }
}
