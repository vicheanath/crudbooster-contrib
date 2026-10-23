<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopOrdersController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_orders';
        $this->primary_key = 'id';
        $this->title_field = 'order_number';
        $this->col = array (
  0 => 
  array (
    'label' => 'Order Number',
    'name' => 'order_number',
  ),
  1 => 
  array (
    'label' => 'Customer',
    'name' => 'shop_customer_id',
    'join' => 'shop_customers,name',
  ),
  2 => 
  array (
    'label' => 'Status',
    'name' => 'status',
  ),
  3 => 
  array (
    'label' => 'Total',
    'name' => 'total',
  ),
  4 => 
  array (
    'label' => 'Placed At',
    'name' => 'placed_at',
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Customer',
    'name' => 'shop_customer_id',
    'type' => 'select2',
    'datatable' => 'shop_customers,name',
    'validation' => 'required',
  ),
  1 => 
  array (
    'label' => 'Order Number',
    'name' => 'order_number',
    'type' => 'text',
    'validation' => 'required',
  ),
  2 => 
  array (
    'label' => 'Status',
    'name' => 'status',
    'type' => 'select',
    'dataenum' => 'pending;paid;shipped;cancelled',
  ),
  3 => 
  array (
    'label' => 'Total',
    'name' => 'total',
    'type' => 'money',
  ),
  4 => 
  array (
    'label' => 'Placed At',
    'name' => 'placed_at',
    'type' => 'datetime',
  ),
  5 => 
  array (
    'label' => 'Notes',
    'name' => 'notes',
    'type' => 'textarea',
  ),
);
    }
}