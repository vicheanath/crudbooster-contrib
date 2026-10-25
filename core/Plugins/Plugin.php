<?php

namespace CrudBooster\Plugins;

use CrudBooster\Panel\Panel;

/**
 * A reusable bundle of resources, pages, navigation and render hooks:
 *
 *     Panel::plugin(new BlogPlugin);
 *
 * register() runs when the plugin is added, boot() once every service provider has booted and
 * before the panel's routes are registered.
 */
interface Plugin
{
    public function getId(): string;

    public function register(Panel $panel): void;

    public function boot(Panel $panel): void;
}
