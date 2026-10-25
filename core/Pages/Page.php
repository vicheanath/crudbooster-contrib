<?php

namespace CrudBooster\Pages;

use CrudBooster\Auth\Authorizer;
use Illuminate\Support\Str;

/**
 * A custom admin page in the CRUDBooster layout, for anything that is not a list of records:
 * reports, settings forms, tools.
 *
 *     class SalesReport extends Page
 *     {
 *         protected static ?string $navigationIcon = 'fa fa-line-chart';
 *         protected static string $view = 'admin.sales-report';
 *
 *         public function data(OrderStats $stats): array   // services are injected
 *         {
 *             return ['total' => $stats->total()];
 *         }
 *     }
 *
 * The view holds only the page body and receives what data() returns (data() is optional). A
 * page that defines submit() also accepts POST requests to its URL. Both are called through the
 * container, so they may ask for the Request and any service; submit() returns a response.
 */
abstract class Page
{
    protected static ?string $slug = null;

    protected static ?string $title = null;

    protected static string $view;

    protected static ?string $navigationIcon = 'fa fa-file-o';

    protected static ?string $navigationGroup = null;

    protected static int $navigationSort = 0;

    protected static bool $shouldRegisterNavigation = true;

    public static function getSlug(): string
    {
        return static::$slug ?? Str::kebab(class_basename(static::class));
    }

    public static function getTitle(): string
    {
        return static::$title ?? Str::headline(class_basename(static::class));
    }

    public static function getView(): string
    {
        return static::$view;
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon;
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }

    public static function getNavigationSort(): int
    {
        return static::$navigationSort;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::$shouldRegisterNavigation;
    }

    public static function url(array $parameters = []): string
    {
        return route(static::getRouteName(), $parameters);
    }

    public static function getRouteName(): string
    {
        return 'cb.pages.'.static::getSlug();
    }

    public static function acceptsSubmissions(): bool
    {
        return method_exists(static::class, 'submit');
    }

    /**
     * Who may open the page; by default the "view" privilege on the module with this slug.
     */
    public function can(): bool
    {
        return app(Authorizer::class)->allows('view', static::getSlug(), static::class);
    }
}
