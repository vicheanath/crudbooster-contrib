<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopOrderItemsController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_order_items';
        $this->primary_key = 'id';
        $this->title_field = 'id';
        $this->col = array (
  0 => 
  array (
    'label' => 'Order',
    'name' => 'shop_order_id',
  ),
  1 => 
  array (
    'label' => 'Product',
    'name' => 'shop_product_id',
  ),
  2 => 
  array (
    'label' => 'Quantity',
    'name' => 'quantity',
  ),
  3 => 
  array (
    'label' => 'Unit Price',
    'name' => 'unit_price',
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Order',
    'name' => 'shop_order_id',
    'type' => 'select',
    'datatable' => 'shop_orders,order_number',
  ),
  1 => 
  array (
    'label' => 'Product',
    'name' => 'shop_product_id',
    'type' => 'select',
    'datatable' => 'shop_products,name',
  ),
  2 => 
  array (
    'label' => 'Quantity',
    'name' => 'quantity',
    'type' => 'number',
  ),
  3 => 
  array (
    'label' => 'Unit Price',
    'name' => 'unit_price',
    'type' => 'money',
  ),
);
    }
}