<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Model;

abstract class CrudboosterModel extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
