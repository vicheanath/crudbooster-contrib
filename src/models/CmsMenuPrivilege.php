<?php

namespace crocodicstudio\crudbooster\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsMenuPrivilege extends CrudboosterModel
{
    protected $table = 'cms_menus_privileges';

    public function menu(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'id_cms_menus');
    }

    public function privilege(): BelongsTo
    {
        return $this->belongsTo(CmsPrivilege::class, 'id_cms_privileges');
    }
}
