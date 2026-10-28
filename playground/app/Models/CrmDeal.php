<?php

namespace App\Models;

use crocodicstudio\crudbooster\models\CmsUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmDeal extends Model
{
    public const STAGES = [
        'lead' => 'Lead',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ];

    protected $fillable = ['stage', 'probability', 'closed_at'];

    protected $casts = ['amount' => 'decimal:2', 'expected_close_on' => 'date', 'closed_at' => 'datetime'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CrmCompany::class, 'crm_company_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(CmsUser::class, 'owner_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->stage, ['won', 'lost'], true);
    }

    /** Closes the deal as won or lost. */
    public function close(string $stage): void
    {
        $this->update(['stage' => $stage, 'probability' => $stage === 'won' ? 100 : 0, 'closed_at' => now()]);
    }
}
