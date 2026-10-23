<?php

namespace crocodicstudio\crudbooster\contracts\Navigation;

use Illuminate\Support\Collection;

interface MenuRepositoryInterface
{
    /**
     * The current privilege's active top-level menus, each with `url`, `url_path`, `is_broken` and,
     * when it has any, `children` built the same way. A menu whose target cannot be resolved links to "#".
     */
    public function sidebar(): Collection;

    /**
     * The current privilege's active dashboard menu with its `url`, or an object with null id, color and
     * url when there is none (the sidebar then shows the default Dashboard link).
     */
    public function dashboard(): object;
}
