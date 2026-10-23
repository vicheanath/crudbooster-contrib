<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\modern\Column;
use crocodicstudio\crudbooster\modern\CrudController;
use crocodicstudio\crudbooster\modern\CrudDefinition;
use crocodicstudio\crudbooster\modern\Field;

// The products module written with the fluent CrudController API.
class AdminCbFluentProductsController extends CrudController
{
    protected function setup(CrudDefinition $crud): void
    {
        $crud->table('cb_products')
            ->titleField('name')
            ->orderBy('price', 'desc')
            ->limit(5)
            ->column('Name', 'name')
            ->column(Column::make('Category', 'id_cb_categories')->join('cb_categories,name'))
            ->column(Column::make('Note', 'note')->strLimit(8))
            ->fields(
                Field::text('name')->rules('required|min:3|unique'),
                Field::money('price'),
                Field::select('id_cb_categories', 'Category')->datatable('cb_categories,name'),
                Field::checkbox('tags')->datatable('cb_tags,name')->relationshipTable('cb_product_tags'),
                Field::textarea('note'),
            )
            ->withoutButtons('export', 'import')
            ->set('show_numbering', true);
    }

    public function hook_before_add(&$postdata)
    {
        $postdata['code'] = 'FLUENT';
    }
}
