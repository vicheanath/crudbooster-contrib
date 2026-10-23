<?php

namespace crocodicstudio\crudbooster\contracts\Settings;

interface SettingsRepositoryInterface
{
    /**
     * The content of the cms_settings row `$name`, or null when there is no such setting.
     */
    public function get(string $name): mixed;

    /**
     * Drops the cached value so the next get() reads the database.
     */
    public function forget(string $name): void;
}
