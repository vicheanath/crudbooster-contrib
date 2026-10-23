<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopCustomersController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_customers';
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
    'label' => 'Email',
    'name' => 'email',
  ),
  2 => 
  array (
    'label' => 'Phone',
    'name' => 'phone',
  ),
  3 => 
  array (
    'label' => 'Status',
    'name' => 'status',
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Name',
    'name' => 'name',
    'type' => 'text',
    'validation' => 'required',
  ),
  1 => 
  array (
    'label' => 'Email',
    'name' => 'email',
    'type' => 'email',
    'validation' => 'required|email',
  ),
  2 => 
  array (
    'label' => 'Phone',
    'name' => 'phone',
    'type' => 'text',
  ),
  3 => 
  array (
    'label' => 'Address',
    'name' => 'address',
    'type' => 'textarea',
  ),
  4 => 
  array (
    'label' => 'Status',
    'name' => 'status',
    'type' => 'select',
    'dataenum' => 'active;inactive',
  ),
);
    }
}