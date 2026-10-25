<?php

namespace CrudBooster\Console;

use crocodicstudio\crudbooster\models\CmsModul;
use CrudBooster\Panel\Panel;
use Illuminate\Console\Command;

/**
 * Adds a cms_moduls row for every resource and page, so Privileges > Edit can grant non-superadmin
 * admins access to them. The rows have no controller, so the classic router leaves them alone.
 */
class SyncPanelCommand extends Command
{
    protected $signature = 'crudbooster:sync-panel';

    protected $description = 'Register the panel\'s resources and pages as modules, to assign privileges to them';

    public function handle(Panel $panel): int
    {
        $rows = [];
        foreach ($panel->getResources() as $resource) {
            $rows[] = [$resource::getSlug(), $resource::getPluralLabel(), $resource::getNavigationIcon(), $resource::newModel()->getTable()];
        }
        foreach ($panel->getPages() as $page) {
            $rows[] = [$page::getSlug(), $page::getTitle(), $page::getNavigationIcon(), null];
        }

        foreach ($rows as [$slug, $name, $icon, $table]) {
            $module = CmsModul::query()->forPath($slug)->notDeleted()->first();

            if ($module && $module->controller) {
                $this->components->warn("Skipped \"$slug\": the classic module [$module->controller] uses this path.");

                continue;
            }

            $values = ['name' => $name, 'icon' => $icon, 'table_name' => $table, 'controller' => null, 'is_protected' => 0, 'is_active' => 1];
            if ($module) {
                $module->forceFill($values)->save();
                $this->components->twoColumnDetail($name, 'updated');
            } else {
                CmsModul::query()->forceCreate(['path' => $slug, 'created_at' => now()] + $values);
                $this->components->twoColumnDetail($name, 'added');
            }
        }

        if (! $rows) {
            $this->components->info('No resources or pages are registered.');
        }

        return self::SUCCESS;
    }
}
