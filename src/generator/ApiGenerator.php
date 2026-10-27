<?php

namespace crocodicstudio\crudbooster\generator;

use crocodicstudio\crudbooster\contracts\Generator\ApiGeneratorInterface;

/**
 * @deprecated 7.2 Write API endpoints as Laravel routes and controllers. See docs/en/deprecations.md.
 */
final class ApiGenerator implements ApiGeneratorInterface
{
    public function generate(string $name, string $table, string $permalink, string $methodType = 'post'): void
    {
        $source = strtr(file_get_contents(__DIR__.'/stubs/api_controller.stub'), [
            '{{name}}' => $name,
            '{{table}}' => $table,
            '{{permalink}}' => $permalink,
            '{{method_type}}' => $methodType,
        ]);

        file_put_contents(base_path('app/Http/Controllers/').'Api'.$name.'Controller.php', trim($source));
    }
}
