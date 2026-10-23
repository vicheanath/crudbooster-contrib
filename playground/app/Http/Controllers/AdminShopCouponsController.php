<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopCouponsController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_coupons';
        $this->primary_key = 'id';
        $this->title_field = 'code';
        $this->col = array (
  0 => 
  array (
    'label' => 'Code',
    'name' => 'code',
  ),
  1 => 
  array (
    'label' => 'Type',
    'name' => 'discount_type',
  ),
  2 => 
  array (
    'label' => 'Value',
    'name' => 'discount_value',
  ),
  3 => 
  array (
    'label' => 'Expires',
    'name' => 'expires_on',
  ),
  4 => 
  array (
    'label' => 'Active',
    'name' => 'is_active',
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Code',
    'name' => 'code',
    'type' => 'text',
    'validation' => 'required',
  ),
  1 => 
  array (
    'label' => 'Type',
    'name' => 'discount_type',
    'type' => 'radio',
    'dataenum' => 'percentage;fixed',
  ),
  2 => 
  array (
    'label' => 'Value',
    'name' => 'discount_value',
    'type' => 'money',
  ),
  3 => 
  array (
    'label' => 'Expires',
    'name' => 'expires_on',
    'type' => 'date',
  ),
  4 => 
  array (
    'label' => 'Active',
    'name' => 'is_active',
    'type' => 'checkbox',
  ),
);
    }
}