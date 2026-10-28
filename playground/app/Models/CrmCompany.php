<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmCompany extends Model
{
    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(CrmDeal::class);
    }
}
