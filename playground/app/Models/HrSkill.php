<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HrSkill extends Model
{
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(HrEmployee::class, 'hr_employee_skill');
    }
}
