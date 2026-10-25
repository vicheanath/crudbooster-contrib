<?php

namespace CrudBooster\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakePageCommand extends Command
{
    protected $signature = 'crudbooster:make-page
        {name : The page class, e.g. SalesReport}
        {--force : Overwrite existing files}';

    protected $description = 'Create a custom CRUDBooster admin page and its view';

    public function handle(Filesystem $files): int
    {
        $class = Str::studly(class_basename($this->argument('name')));
        $slug = Str::kebab($class);
        $view = 'admin.pages.'.$slug;
        $classPath = app_path('Admin/Pages/'.$class.'.php');
        $viewPath = resource_path('views/admin/pages/'.$slug.'.blade.php');

        foreach ([$classPath, $viewPath] as $path) {
            if ($files->exists($path) && ! $this->option('force')) {
                $this->components->error("$path already exists. Pass --force to overwrite it.");

                return self::FAILURE;
            }
        }

        $files->ensureDirectoryExists(dirname($classPath));
        $files->put($classPath, strtr($files->get(__DIR__.'/stubs/page.stub'), [
            '{{ namespace }}' => 'App\\Admin\\Pages',
            '{{ class }}' => $class,
            '{{ view }}' => $view,
        ]));

        $files->ensureDirectoryExists(dirname($viewPath));
        $files->put($viewPath, strtr($files->get(__DIR__.'/stubs/page-view.stub'), [
            '{{ title }}' => Str::headline($class),
            '{{ path }}' => 'resources/views/admin/pages/'.$slug.'.blade.php',
        ]));

        $this->components->info("Page [$classPath] and view [$viewPath] created.");
        $this->line('  Open '.url(config('crudbooster.ADMIN_PATH').'/'.$slug));

        return self::SUCCESS;
    }
}
