<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopCategoriesController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_categories';
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
    'label' => 'Slug',
    'name' => 'slug',
  ),
  2 => 
  array (
    'label' => 'Active',
    'name' => 'is_active',
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
    'label' => 'Slug',
    'name' => 'slug',
    'type' => 'text',
    'validation' => 'required',
  ),
  2 => 
  array (
    'label' => 'Description',
    'name' => 'description',
    'type' => 'textarea',
  ),
  3 => 
  array (
    'label' => 'Active',
    'name' => 'is_active',
    'type' => 'checkbox',
  ),
);
    }
}