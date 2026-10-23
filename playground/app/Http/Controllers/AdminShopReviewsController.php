<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

class AdminShopReviewsController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_reviews';
        $this->primary_key = 'id';
        $this->title_field = 'id';
        $this->col = array (
  0 => 
  array (
    'label' => 'Product',
    'name' => 'shop_product_id',
  ),
  1 => 
  array (
    'label' => 'Customer',
    'name' => 'shop_customer_id',
  ),
  2 => 
  array (
    'label' => 'Rating',
    'name' => 'rating',
  ),
  3 => 
  array (
    'label' => 'Published',
    'name' => 'is_published',
  ),
);
        $this->form = array (
  0 => 
  array (
    'label' => 'Product',
    'name' => 'shop_product_id',
    'type' => 'select',
    'datatable' => 'shop_products,name',
  ),
  1 => 
  array (
    'label' => 'Customer',
    'name' => 'shop_customer_id',
    'type' => 'select',
    'datatable' => 'shop_customers,name',
  ),
  2 => 
  array (
    'label' => 'Rating',
    'name' => 'rating',
    'type' => 'number',
  ),
  3 => 
  array (
    'label' => 'Comment',
    'name' => 'comment',
    'type' => 'textarea',
  ),
  4 => 
  array (
    'label' => 'Published',
    'name' => 'is_published',
    'type' => 'checkbox',
  ),
);
    }
}