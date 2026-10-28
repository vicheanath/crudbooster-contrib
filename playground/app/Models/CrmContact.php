<?php

namespace App\Models;

use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmContact extends Model
{
    protected $fillable = ['is_subscribed'];

    protected $casts = ['is_subscribed' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CrmCompany::class, 'crm_company_id');
    }

    /** The CRUDBooster admin who looks after this contact. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(CmsUser::class, 'owner_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(CrmTag::class, 'crm_contact_tag');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class);
    }
}
