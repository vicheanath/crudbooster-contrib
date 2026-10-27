<?php

namespace crocodicstudio\crudbooster\contracts\Generator;

/**
 * @deprecated 7.2 Write API endpoints as Laravel routes and controllers. See docs/en/deprecations.md.
 */
interface ApiGeneratorInterface
{
    /**
     * Writes app/Http/Controllers/Api{Name}Controller.php, an ApiController for `$table` served at `$permalink`.
     */
    public function generate(string $name, string $table, string $permalink, string $methodType = 'post'): void;
}
