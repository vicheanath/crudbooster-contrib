<?php

namespace App\Admin\Resources\Crm;

use App\Models\CrmDeal;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Forms\Section;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * CRM example: deals moving through a pipeline. Closing a deal (won or lost) is an action, and
 * choosing won or lost in the form closes it the same way (beforeUpdate()).
 */
class DealResource extends Resource
{
    public const COLORS = ['lead' => 'secondary', 'qualified' => 'info', 'proposal' => 'primary', 'negotiation' => 'warning', 'won' => 'success', 'lost' => 'danger'];

    protected static string $model = CrmDeal::class;

    protected static ?string $slug = 'crm-deals';

    protected static ?string $label = 'Deal';

    protected static ?string $navigationIcon = 'fa fa-briefcase';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public function __construct(private readonly AuthenticationServiceInterface $auth)
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('title')->searchable()->sortable()->url(fn (CrmDeal $record) => static::url('view', $record)),
                Columns\TextColumn::make('company.name')->label('Company')->searchable(),
                Columns\BadgeColumn::make('stage')->sortable()->formatStateUsing(fn (string $state) => CrmDeal::STAGES[$state] ?? $state)->colors(self::COLORS),
                Columns\TextColumn::make('amount')->money('$')->sortable()->alignEnd(),
                Columns\TextColumn::make('probability')->suffix('%')->sortable()->alignEnd(),
                Columns\TextColumn::make('expected_close_on')->label('Expected close')->date('d M Y')->sortable()->placeholder('—'),
                Columns\TextColumn::make('owner.name')->label('Owner')->placeholder('—'),
            ])
            ->filters([
                Filters\SelectFilter::make('stage')->options(CrmDeal::STAGES),
                Filters\SelectFilter::make('company')->relationship('company', 'name'),
                Filters\SelectFilter::make('owner')->relationship('owner', 'name'),
                Filters\Filter::make('open')->label('Open deals only')->query(fn (Builder $query) => $query->whereNotIn('stage', ['won', 'lost'])),
                Filters\Filter::make('mine')->label('My deals')->query(fn (Builder $query) => $query->where('owner_id', $this->auth->currentUserId())),
            ])
            ->actions([
                Action::make('won')->label('Mark won')->icon('fa fa-trophy')->color('success')
                    ->visible(fn (CrmDeal $record) => $record->isOpen())
                    ->authorize('update')
                    ->action(fn (CrmDeal $record) => $record->close('won'))
                    ->successMessage('Deal won.'),
                Action::make('lost')->label('Mark lost')->icon('fa fa-thumbs-down')->color('danger')
                    ->visible(fn (CrmDeal $record) => $record->isOpen())
                    ->authorize('update')
                    ->requiresConfirmation('Close this deal as lost?')
                    ->action(fn (CrmDeal $record) => $record->close('lost'))
                    ->successMessage('Deal closed as lost.'),
                Action::make('activities')->icon('fa fa-tasks')
                    ->url(fn (CrmDeal $record) => ActivityResource::url(parameters: ['filters' => ['deal' => $record->id]])),
            ])
            ->defaultSort('expected_close_on');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('title')->required()->maxLength(150),
            Fields\Select::make('crm_company_id')->label('Company')->relationship('company', 'name')->required()->columnSpan(6),
            Fields\Select::make('crm_contact_id')->label('Contact')->relationship('contact', 'name')->columnSpan(6),
            Section::make('Pipeline')->schema([
                Fields\Select::make('stage')->options(CrmDeal::STAGES)->default('lead')->required()->columnSpan(4),
                Fields\TextInput::make('amount')->numeric()->prefix('$')->required()->rules('min:0')->columnSpan(4),
                Fields\TextInput::make('probability')->numeric()->suffix('%')->default(10)->rules('integer|between:0,100')->columnSpan(4),
                Fields\DatePicker::make('expected_close_on')->label('Expected close')->columnSpan(6),
                Fields\Select::make('owner_id')->label('Owner')->relationship('owner', 'name')
                    ->default(fn () => $this->auth->currentUserId())->columnSpan(6),
            ]),
            Fields\Textarea::make('notes')->rows(4),
        ]);
    }

    public function beforeUpdate(array $data, Model $record): array
    {
        // Moving a deal to won or lost in the form closes it, like the actions do.
        if (in_array($data['stage'] ?? null, ['won', 'lost'], true) && $record->isOpen()) {
            $data['closed_at'] = now();
            $data['probability'] = $data['stage'] === 'won' ? 100 : 0;
        }

        return $data;
    }
}
