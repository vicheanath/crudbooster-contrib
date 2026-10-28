<?php

namespace App\Admin\Resources\Crm;

use App\Models\CrmCompany;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * CRM example: companies with their number of contacts and the value of their open deals,
 * both computed in modifyQuery().
 */
class CompanyResource extends Resource
{
    public const INDUSTRIES = [
        'software' => 'Software', 'retail' => 'Retail', 'finance' => 'Finance',
        'health' => 'Health', 'manufacturing' => 'Manufacturing', 'education' => 'Education',
    ];

    protected static string $model = CrmCompany::class;

    protected static ?string $slug = 'crm-companies';

    protected static ?string $label = 'Company';

    protected static ?string $pluralLabel = 'Companies';

    protected static ?string $navigationIcon = 'fa fa-building';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('contacts')
            ->withSum(['deals as open_pipeline' => fn (Builder $deals) => $deals->whereNotIn('stage', ['won', 'lost'])], 'amount');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable()->url(fn (CrmCompany $record) => static::url('view', $record)),
                Columns\BadgeColumn::make('industry')->formatStateUsing(fn (?string $state) => self::INDUSTRIES[$state] ?? $state)->colors([], 'info'),
                Columns\TextColumn::make('city')->searchable(),
                Columns\TextColumn::make('website')->url(fn (CrmCompany $record) => $record->website, openInNewTab: true)->placeholder('—'),
                Columns\TextColumn::make('contacts_count')->label('Contacts')->sortable()->alignEnd(),
                Columns\TextColumn::make('open_pipeline')->label('Open pipeline')->money('$')->sortable()->alignEnd(),
            ])
            ->filters([
                Filters\SelectFilter::make('industry')->options(self::INDUSTRIES),
            ])
            ->actions([
                Action::make('contacts')->icon('fa fa-book')
                    ->url(fn (CrmCompany $record) => ContactResource::url(parameters: ['filters' => ['company' => $record->id]])),
                Action::make('deals')->icon('fa fa-briefcase')
                    ->url(fn (CrmCompany $record) => DealResource::url(parameters: ['filters' => ['company' => $record->id]])),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(150)->columnSpan(8),
            Fields\Select::make('industry')->options(self::INDUSTRIES)->columnSpan(4),
            Fields\TextInput::make('website')->url()->placeholder('https://')->columnSpan(6),
            Fields\TextInput::make('phone')->tel()->columnSpan(6),
            Fields\TextInput::make('city')->columnSpan(6),
            Fields\TextInput::make('employees')->numeric()->rules('integer|min:1')->suffix('people')->columnSpan(6),
        ]);
    }
}
