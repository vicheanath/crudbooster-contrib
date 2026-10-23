<?php

namespace crocodicstudio\crudbooster\contracts\Generator;

interface ApiGeneratorInterface
{
    /**
     * Writes app/Http/Controllers/Api{Name}Controller.php, an ApiController for `$table` served at `$permalink`.
     */
    public function generate(string $name, string $table, string $permalink, string $methodType = 'post'): void;
}
