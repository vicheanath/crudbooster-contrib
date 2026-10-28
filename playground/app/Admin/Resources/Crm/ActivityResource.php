<?php

namespace App\Admin\Resources\Crm;

use App\Models\CrmActivity;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * CRM example: calls, emails, meetings and tasks, with an "overdue" filter and a complete action.
 */
class ActivityResource extends Resource
{
    public const TYPES = ['call' => 'Call', 'email' => 'Email', 'meeting' => 'Meeting', 'task' => 'Task'];

    protected static string $model = CrmActivity::class;

    protected static ?string $slug = 'crm-activities';

    protected static ?string $label = 'Activity';

    protected static ?string $pluralLabel = 'Activities';

    protected static ?string $navigationIcon = 'fa fa-tasks';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'subject';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\BadgeColumn::make('type')->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state)
                    ->colors(['call' => 'info', 'email' => 'secondary', 'meeting' => 'primary', 'task' => 'warning']),
                Columns\TextColumn::make('subject')->searchable(),
                Columns\TextColumn::make('deal.title')->label('Deal')->placeholder('—'),
                Columns\TextColumn::make('contact.name')->label('Contact')->placeholder('—'),
                Columns\TextColumn::make('due_at')->label('Due')->dateTime('d M Y H:i')->sortable()->placeholder('—'),
                Columns\BooleanColumn::make('is_done')->label('Done'),
            ])
            ->filters([
                Filters\SelectFilter::make('type')->options(self::TYPES),
                Filters\SelectFilter::make('deal')->relationship('deal', 'title'),
                Filters\TernaryFilter::make('is_done')->label('Done'),
                Filters\Filter::make('overdue')->query(fn (Builder $query) => $query->where('is_done', false)->where('due_at', '<', now())),
            ])
            ->actions([
                Action::make('complete')->icon('fa fa-check')->color('success')
                    ->visible(fn (CrmActivity $record) => ! $record->is_done)
                    ->action(fn (CrmActivity $record) => $record->update(['is_done' => true]))
                    ->successMessage('Activity completed.'),
            ])
            ->bulkActions([
                BulkAction::make('complete')->icon('fa fa-check')
                    ->action(fn (Collection $records) => $records->each->update(['is_done' => true]))
                    ->successMessage('Activities completed.'),
            ])
            ->defaultSort('due_at');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\Radio::make('type')->options(self::TYPES)->default('call')->required(),
            Fields\TextInput::make('subject')->required()->maxLength(150),
            Fields\Select::make('crm_deal_id')->label('Deal')->relationship('deal', 'title')->columnSpan(6),
            Fields\Select::make('crm_contact_id')->label('Contact')->relationship('contact', 'name')->columnSpan(6),
            Fields\DatePicker::make('due_at')->label('Due')->withTime()->columnSpan(6),
            Fields\Toggle::make('is_done')->label('Done')->columnSpan(6),
            Fields\Textarea::make('notes')->rows(3),
        ]);
    }
}
