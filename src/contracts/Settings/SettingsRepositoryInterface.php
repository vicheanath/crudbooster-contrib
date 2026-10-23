<?php

namespace crocodicstudio\crudbooster\contracts\Settings;

use Illuminate\Support\Collection;

interface SettingsRepositoryInterface
{
    /**
     * The content of the cms_settings row `$name`, or null when there is no such setting.
     */
    public function get(string $name): mixed;

    /**
     * Stores new content for an existing setting and drops its cached value.
     */
    public function set(string $name, mixed $content): void;

    /**
     * Drops the cached value so the next get() reads the database.
     */
    public function forget(string $name): void;

    /**
     * The settings of one group (a settings page), as models.
     */
    public function inGroup(string $group): Collection;

    /**
     * The distinct group names, one settings page each.
     */
    public function groups(): Collection;
}
