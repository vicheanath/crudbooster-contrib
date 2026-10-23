<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\controllers\CBController;

// A module whose hidden required field is filled by hook_before_add, a common 5.x pattern.
class AdminHookFilledController extends CBController
{
    public function cbInit()
    {
        $this->table = 'cms_email_templates';
        $this->primary_key = 'id';
        $this->title_field = 'name';
        $this->col = [['label' => 'Name', 'name' => 'name']];
        $this->form = [
            ['label' => 'Description', 'name' => 'description', 'type' => 'hidden', 'validation' => 'required|min:1|max:255'],
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'validation' => 'required|min:3|max:255'],
            ['label' => 'Slug', 'name' => 'slug', 'type' => 'text', 'validation' => 'required'],
        ];
    }

    public function hook_before_add(&$postdata)
    {
        $postdata['description'] = 'filled by hook';
    }
}
