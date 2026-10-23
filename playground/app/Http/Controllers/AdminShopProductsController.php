<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopProductsController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_products';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->col = array (
  0 => 
  array (
    'label' => 'Name',
    'name' => 'name',
  ),
  1 => 
  array (
    'label' => 'SKU',
    'name' => 'sku',
  ),
  2 => 
  array (
    'label' => 'Price',
    'name' => 'price',
  ),
  3 => 
  array (
    'label' => 'Stock',
    'name' => 'stock',
  ),
  4 => 
  array (
    'label' => 'Featured',
    'name' => 'is_featured',
  ),
  5 => 
  array (
    'label' => 'Image',
    'name' => 'image',
    'image' => 1,
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Category',
    'name' => 'shop_category_id',
    'type' => 'select',
    'datatable' => 'shop_categories,name',
    'validation' => 'required',
  ),
  1 => 
  array (
    'label' => 'Name',
    'name' => 'name',
    'type' => 'text',
    'validation' => 'required',
  ),
  2 => 
  array (
    'label' => 'SKU',
    'name' => 'sku',
    'type' => 'text',
    'validation' => 'required',
  ),
  3 => 
  array (
    'label' => 'Price',
    'name' => 'price',
    'type' => 'money',
    'validation' => 'required|numeric',
  ),
  4 => 
  array (
    'label' => 'Stock',
    'name' => 'stock',
    'type' => 'number',
  ),
  5 => 
  array (
    'label' => 'Description',
    'name' => 'description',
    'type' => 'wysiwyg',
  ),
  6 => 
  array (
    'label' => 'Image',
    'name' => 'image',
    'type' => 'upload',
  ),
  7 => 
  array (
    'label' => 'Featured',
    'name' => 'is_featured',
    'type' => 'checkbox',
  ),
  8 => 
  array (
    'label' => 'Available On',
    'name' => 'available_on',
    'type' => 'date',
  ),
);
    }
}