<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * Every CRUDBooster form field type on one form, for manual testing. Each label names the type it shows;
 * lookups reuse the e-commerce tables. "filemanager" is left out: it has no view component yet.
 */
class AdminFieldShowcasesController extends CBController
{
    public function cbInit()
    {
        $this->table = 'field_showcases';
        $this->primary_key = 'id';
        $this->title_field = 'title';
        $this->orderby = 'id,desc';
        $this->button_export = true;
        $this->button_import = false;

        $this->col = [];
        $this->col[] = ['label' => 'Title', 'name' => 'title'];
        $this->col[] = ['label' => 'Cover', 'name' => 'cover_image', 'image' => true];
        $this->col[] = ['label' => 'Status', 'name' => 'status'];
        $this->col[] = ['label' => 'Category', 'name' => 'shop_category_id', 'join' => 'shop_categories,name'];
        $this->col[] = ['label' => 'Customer', 'name' => 'shop_customer_id', 'join' => 'shop_customers,name'];
        $this->col[] = ['label' => 'Price', 'name' => 'price', 'callback' => fn ($row) => $row->price === null ? '' : number_format($row->price, 2)];
        $this->col[] = ['label' => 'Platforms', 'name' => 'platforms'];
        $this->col[] = ['label' => 'Meeting', 'name' => 'meeting_at'];

        $this->form = [];

        $this->form[] = ['label' => 'Text inputs', 'type' => 'header', 'icon' => 'fa fa-font'];
        $this->form[] = ['label' => 'Title (text)', 'name' => 'title', 'type' => 'text', 'validation' => 'required|string|min:3|max:255', 'placeholder' => 'Required, 3 to 255 characters'];
        $this->form[] = ['label' => 'Email (email)', 'name' => 'email', 'type' => 'email', 'validation' => 'nullable|email'];
        $this->form[] = ['label' => 'Password (password)', 'name' => 'password', 'type' => 'password', 'help' => 'Stored hashed; leave empty when editing to keep it'];
        $this->form[] = ['label' => 'Quantity (number)', 'name' => 'quantity', 'type' => 'number', 'validation' => 'nullable|integer|min:0', 'step' => 1];
        $this->form[] = ['label' => 'Price (money)', 'name' => 'price', 'type' => 'money', 'priceformat_parameters' => ['prefix' => '$ ', 'thousandsSeparator' => ',', 'centsSeparator' => '.', 'centsLimit' => 2]];
        $this->form[] = ['label' => 'Aliases (multitext)', 'name' => 'aliases', 'type' => 'multitext', 'max_fields' => 4, 'help' => 'Up to 4 values, saved separated by "|"'];
        $this->form[] = ['label' => 'Reference (hidden)', 'name' => 'reference', 'type' => 'hidden', 'value' => 'PLAYGROUND'];

        $this->form[] = ['label' => 'Date and time', 'type' => 'header', 'icon' => 'fa fa-calendar'];
        $this->form[] = ['label' => 'Birth date (date)', 'name' => 'birth_date', 'type' => 'date', 'validation' => 'nullable|date'];
        $this->form[] = ['label' => 'Meeting at (datetime)', 'name' => 'meeting_at', 'type' => 'datetime', 'validation' => 'nullable|date_format:Y-m-d H:i:s'];
        $this->form[] = ['label' => 'Alarm (time)', 'name' => 'alarm_time', 'type' => 'time', 'validation' => 'nullable|date_format:H:i:s'];

        $this->form[] = ['label' => 'Long text', 'type' => 'header', 'icon' => 'fa fa-align-left'];
        $this->form[] = ['label' => 'Summary (textarea)', 'name' => 'summary', 'type' => 'textarea', 'validation' => 'nullable|string|max:5000'];
        $this->form[] = ['label' => 'Body (wysiwyg)', 'name' => 'body', 'type' => 'wysiwyg'];
        $this->form[] = ['label' => 'Settings (json)', 'name' => 'settings', 'type' => 'json', 'schema' => json_encode([
            'type' => 'object',
            'title' => 'Settings',
            'properties' => [
                'theme' => ['type' => 'string', 'enum' => ['light', 'dark']],
                'notify' => ['type' => 'boolean'],
                'limit' => ['type' => 'integer'],
            ],
        ])];

        $this->form[] = ['label' => 'Single choice', 'type' => 'header', 'icon' => 'fa fa-dot-circle-o'];
        $this->form[] = ['label' => 'Status (radio, enum)', 'name' => 'status', 'type' => 'radio', 'dataenum' => 'draft;published;archived', 'validation' => 'nullable|in:draft,published,archived'];
        $this->form[] = ['label' => 'Priority (radio, value|label)', 'name' => 'priority', 'type' => 'radio', 'dataenum' => '1|Low;2|Medium;3|High'];
        $this->form[] = ['label' => 'Color (select, enum)', 'name' => 'color', 'type' => 'select', 'dataenum' => 'red;green;blue'];
        $this->form[] = ['label' => 'Category (select, table)', 'name' => 'shop_category_id', 'type' => 'select', 'datatable' => 'shop_categories,name', 'datatable_where' => 'is_active = 1', 'datatable_format' => "id,' - ',name"];
        $this->form[] = ['label' => 'Category (select, SQL query)', 'name' => 'query_category_id', 'type' => 'select', 'dataquery' => 'select id as value, name as label from shop_categories order by name'];
        $this->form[] = ['label' => 'Level (select2, enum)', 'name' => 'level', 'type' => 'select2', 'dataenum' => 'beginner;intermediate;expert'];
        $this->form[] = ['label' => 'Customer (select2, ajax table)', 'name' => 'shop_customer_id', 'type' => 'select2', 'datatable' => 'shop_customers,name', 'datatable_ajax' => true];
        $this->form[] = ['label' => 'Product (datamodal)', 'name' => 'shop_product_id', 'type' => 'datamodal', 'datamodal_table' => 'shop_products', 'datamodal_columns' => 'name,sku,price', 'datamodal_columns_alias' => 'Name,SKU,Price', 'datamodal_size' => 'large', 'datamodal_paginate' => 5];

        $this->form[] = ['label' => 'Multiple choice', 'type' => 'header', 'icon' => 'fa fa-check-square-o'];
        $this->form[] = ['label' => 'Platforms (checkbox, enum)', 'name' => 'platforms', 'type' => 'checkbox', 'dataenum' => 'Android;iOS;Web', 'help' => 'Saved separated by ";"'];
        $this->form[] = ['label' => 'Category names (checkbox, table)', 'name' => 'category_names', 'type' => 'checkbox', 'datatable' => 'shop_categories,name', 'help' => 'Saves the chosen names separated by ";"'];
        $this->form[] = ['label' => 'Categories (checkbox, many-to-many)', 'name' => 'categories', 'type' => 'checkbox', 'datatable' => 'shop_categories,name', 'relationship_table' => 'field_showcase_categories'];
        $this->form[] = ['label' => 'Customers (select2, many-to-many)', 'name' => 'customers', 'type' => 'select2', 'datatable' => 'shop_customers,name', 'relationship_table' => 'field_showcase_customers'];

        $this->form[] = ['label' => 'Files', 'type' => 'header', 'icon' => 'fa fa-paperclip'];
        $this->form[] = ['label' => 'Cover image (upload, image)', 'name' => 'cover_image', 'type' => 'upload', 'validation' => 'nullable|image|max:2000', 'resize_width' => 800, 'help' => 'Image up to 2 MB, resized to 800px wide'];
        $this->form[] = ['label' => 'Attachment (upload, file)', 'name' => 'attachment', 'type' => 'upload', 'validation' => 'nullable|mimes:pdf,txt,zip|max:5000', 'upload_encrypt' => true];

        $this->form[] = ['label' => 'Location', 'type' => 'header', 'icon' => 'fa fa-map-marker'];
        $this->form[] = ['label' => 'Address (googlemaps)', 'name' => 'address', 'type' => 'googlemaps', 'latitude' => 'latitude', 'longitude' => 'longitude', 'help' => 'Needs the "Google API Key" setting'];
        $this->form[] = ['label' => 'Latitude', 'name' => 'latitude', 'type' => 'hidden'];
        $this->form[] = ['label' => 'Longitude', 'name' => 'longitude', 'type' => 'hidden'];

        $this->form[] = ['label' => 'Custom and child rows', 'type' => 'header', 'icon' => 'fa fa-cubes', 'collapsed' => true];
        $this->form[] = ['label' => 'Rating (custom)', 'name' => 'rating', 'type' => 'custom', 'html' => $this->ratingInput()];
        $this->form[] = ['label' => 'Items (child)', 'name' => 'items', 'type' => 'child', 'table' => 'field_showcase_items', 'foreign_key' => 'field_showcase_id', 'columns' => [
            ['label' => 'Name (text)', 'name' => 'name', 'type' => 'text', 'required' => true],
            ['label' => 'Note (textarea)', 'name' => 'note', 'type' => 'textarea'],
            ['label' => 'Quantity (number)', 'name' => 'quantity', 'type' => 'number', 'min' => 1, 'max' => 99],
            ['label' => 'Size (radio)', 'name' => 'size', 'type' => 'radio', 'dataenum' => 'S;M;L'],
            ['label' => 'Kind (select, enum)', 'name' => 'kind', 'type' => 'select', 'dataenum' => 'gift;sample;regular'],
            ['label' => 'Category (select, table)', 'name' => 'shop_category_id', 'type' => 'select', 'datatable' => 'shop_categories,name'],
            ['label' => 'Product (datamodal)', 'name' => 'shop_product_id', 'type' => 'datamodal', 'datamodal_table' => 'shop_products', 'datamodal_columns' => 'name,sku', 'datamodal_columns_alias' => 'Name,SKU'],
            ['label' => 'Photo (upload)', 'name' => 'photo', 'type' => 'upload', 'upload_type' => 'image'],
            ['label' => 'Source (hidden)', 'name' => 'source', 'type' => 'hidden'],
        ]];
    }

    /**
     * A plain HTML input rendered by the "custom" type; its value is saved like any other field.
     */
    private function ratingInput(): string
    {
        $id = (int) request()->segment(4);
        $current = $id ? (string) \DB::table('field_showcases')->where('id', $id)->value('rating') : '';
        $options = '';
        foreach (['', '1', '2', '3', '4', '5'] as $value) {
            $selected = $value === $current ? ' selected' : '';
            $label = $value === '' ? 'No rating' : str_repeat('★', (int) $value);
            $options .= "<option value='{$value}'{$selected}>{$label}</option>";
        }

        return "<select name='rating' class='form-control' style='max-width:200px'>{$options}</select>";
    }
}
