<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

// A classic 5.x module that uses most field types, joins, a subquery column and every hook,
// so CrudBehaviourTest pins down how CBController behaves for existing apps.
class AdminCbProductsController extends CBController
{
    public static $calls = [];

    public function cbInit()
    {
        $this->table = 'cb_products';
        $this->title_field = 'name';
        $this->limit = 20;
        $this->orderby = 'name,asc';

        $this->col = [
            ['label' => 'Name', 'name' => 'name'],
            ['label' => 'Category', 'name' => 'id_cb_categories', 'join' => 'cb_categories,name'],
            ['label' => 'Tags', 'name' => '(select count(*) from cb_product_tags where cb_product_tags.cb_products_id = cb_products.id) as tag_count'],
            ['label' => 'Price', 'name' => 'price', 'callback' => function ($row) {
                return 'USD '.$row->price;
            }],
        ];

        $this->form = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'validation' => 'required|min:3|unique:cb_products'],
            ['label' => 'Price', 'name' => 'price', 'type' => 'money'],
            ['label' => 'Category', 'name' => 'id_cb_categories', 'type' => 'select', 'datatable' => 'cb_categories,name'],
            ['label' => 'Colors', 'name' => 'colors', 'type' => 'checkbox', 'dataenum' => 'Red;Green;Blue'],
            ['label' => 'Tags', 'name' => 'tags', 'type' => 'checkbox', 'datatable' => 'cb_tags,name', 'relationship_table' => 'cb_product_tags'],
            ['label' => 'Aliases', 'name' => 'aliases', 'type' => 'multitext'],
            ['label' => 'Address', 'name' => 'address', 'type' => 'googlemaps', 'latitude' => 'lat', 'longitude' => 'lng'],
            ['label' => 'Password', 'name' => 'password', 'type' => 'password'],
            ['label' => 'Note', 'name' => 'note', 'type' => 'textarea'],
            ['label' => 'Code', 'name' => 'code', 'type' => 'text'],
            ['label' => 'Confirm', 'name' => 'confirm', 'type' => 'text', 'exception' => true],
            ['label' => 'Photo', 'name' => 'photo', 'type' => 'upload'],
            ['label' => 'Variants', 'name' => 'variants', 'type' => 'child', 'table' => 'cb_variants', 'foreign_key' => 'cb_products_id', 'columns' => [
                ['label' => 'Sku', 'name' => 'sku', 'type' => 'text'],
                ['label' => 'Qty', 'name' => 'qty', 'type' => 'number'],
            ]],
        ];
    }

    public function hook_query_index(&$query)
    {
        static::$calls[] = 'hook_query_index';
        if (request('only_cheap')) {
            $query->where('cb_products.price', '<', 100);
        }
    }

    public function hook_row_index($index, &$value)
    {
        if (is_string($value) && str_contains($value, 'Discontinued')) {
            $value = $value.' (EOL)';
        }
    }

    public function hook_before_add(&$postdata)
    {
        static::$calls[] = 'hook_before_add';
        $postdata['note'] = ($postdata['note'] ?? '').'[added]';
    }

    public function hook_after_add($id)
    {
        static::$calls[] = 'hook_after_add:'.$id;
    }

    public function hook_before_edit(&$postdata, $id)
    {
        static::$calls[] = 'hook_before_edit:'.$id;
    }

    public function hook_after_edit($id)
    {
        static::$calls[] = 'hook_after_edit:'.$id;
    }

    public function hook_before_delete($id)
    {
        static::$calls[] = 'hook_before_delete:'.json_encode($id);
    }

    public function hook_after_delete($id)
    {
        static::$calls[] = 'hook_after_delete:'.json_encode($id);
    }

    public function actionButtonSelected($id_selected, $button_name)
    {
        static::$calls[] = 'action:'.$button_name.':'.implode(',', $id_selected);
    }
}
