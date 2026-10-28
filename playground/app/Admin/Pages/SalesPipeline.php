<?php

namespace App\Admin\Pages;

use App\Admin\Resources\Crm\ActivityResource;
use App\Admin\Resources\Crm\DealResource;
use App\Models\CrmActivity;
use App\Models\CrmDeal;
use CrudBooster\Pages\Page;

/**
 * CRM example: the value of each pipeline stage, the forecast (amount × probability), won
 * revenue this year and the activities that are due.
 */
class SalesPipeline extends Page
{
    protected static ?string $navigationIcon = 'fa fa-line-chart';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = -1;

    protected static string $view = 'admin.pages.sales-pipeline';

    public function data(): array
    {
        $open = CrmDeal::query()->whereNotIn('stage', ['won', 'lost']);
        $byStage = CrmDeal::query()->selectRaw('stage, count(*) as deals, coalesce(sum(amount), 0) as amount')
            ->groupBy('stage')->get()->keyBy('stage');

        return [
            'stats' => [
                ['label' => 'Open pipeline', 'value' => '$'.number_format((float) (clone $open)->sum('amount')), 'icon' => 'fa fa-filter', 'color' => 'primary', 'url' => DealResource::url(parameters: ['filters' => ['open' => 1]])],
                ['label' => 'Weighted forecast', 'value' => '$'.number_format((float) (clone $open)->selectRaw('coalesce(sum(amount * probability / 100), 0) as forecast')->value('forecast')), 'icon' => 'fa fa-balance-scale', 'color' => 'info', 'url' => null],
                ['label' => 'Won this year', 'value' => '$'.number_format((float) CrmDeal::query()->where('stage', 'won')->where('closed_at', '>=', now()->startOfYear())->sum('amount')), 'icon' => 'fa fa-trophy', 'color' => 'success', 'url' => DealResource::url(parameters: ['filters' => ['stage' => 'won']])],
                ['label' => 'Overdue activities', 'value' => CrmActivity::query()->where('is_done', false)->where('due_at', '<', now())->count(), 'icon' => 'fa fa-bell', 'color' => 'danger', 'url' => ActivityResource::url(parameters: ['filters' => ['overdue' => 1]])],
            ],
            'stages' => collect(CrmDeal::STAGES)->map(fn (string $label, string $stage) => [
                'label' => $label,
                'color' => DealResource::COLORS[$stage],
                'deals' => (int) ($byStage[$stage]->deals ?? 0),
                'amount' => (float) ($byStage[$stage]->amount ?? 0),
                'url' => DealResource::url(parameters: ['filters' => ['stage' => $stage]]),
            ]),
            'upcoming' => CrmActivity::query()->with(['deal', 'contact'])->where('is_done', false)
                ->orderBy('due_at')->limit(8)->get(),
            'activitiesUrl' => ActivityResource::url(),
        ];
    }
}
