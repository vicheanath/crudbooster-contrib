<?php

namespace crocodicstudio\crudbooster\modern;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * A module defined with the fluent API instead of properties:
 *
 *     class AdminProductsController extends CrudController
 *     {
 *         protected function setup(CrudDefinition $crud): void
 *         {
 *             $crud->table('products')->titleField('name')->orderBy('name')
 *                 ->column('Name', 'name')
 *                 ->column(Column::make('Category', 'category_id')->join('categories,name'))
 *                 ->fields(
 *                     Field::text('name')->rules('required|max:255'),
 *                     Field::select('category_id')->datatable('categories,name'),
 *                 );
 *         }
 *     }
 *
 * It is still a CBController: routes, views, hook_*() methods and actions work the same.
 *
 * @deprecated 7.2 Use a resource (CrudBooster\Resources\Resource, `php artisan crudbooster:make-resource`):
 *     its table() and form() replace setup(). See docs/en/deprecations.md.
 */
abstract class CrudController extends CBController
{
    abstract protected function setup(CrudDefinition $crud): void;

    public function cbInit()
    {
        $crud = new CrudDefinition;
        $this->setup($crud);
        $crud->toConfig()->applyTo($this);
    }
}
