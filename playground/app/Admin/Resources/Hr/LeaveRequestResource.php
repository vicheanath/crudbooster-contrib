<?php

namespace App\Admin\Resources\Hr;

use App\Models\HrLeaveRequest;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * HR example: leave requests with an approve / reject workflow. New requests always start
 * as pending (beforeCreate), and only pending requests can be decided.
 */
class LeaveRequestResource extends Resource
{
    public const TYPES = ['annual' => 'Annual', 'sick' => 'Sick', 'unpaid' => 'Unpaid', 'parental' => 'Parental'];

    public const STATUSES = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    protected static string $model = HrLeaveRequest::class;

    protected static ?string $slug = 'hr-leave-requests';

    protected static ?string $label = 'Leave request';

    protected static ?string $navigationIcon = 'fa fa-calendar-check-o';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('employee.name')->label('Employee')->searchable(),
                Columns\BadgeColumn::make('type')->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state)
                    ->colors(['annual' => 'primary', 'sick' => 'danger', 'unpaid' => 'secondary', 'parental' => 'info']),
                Columns\TextColumn::make('starts_on')->label('From')->date('d M Y')->sortable(),
                Columns\TextColumn::make('ends_on')->label('To')->date('d M Y')->sortable(),
                Columns\TextColumn::make('days')->state(fn (HrLeaveRequest $record) => $record->days())->alignEnd(),
                Columns\BadgeColumn::make('status')->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->colors(['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']),
            ])
            ->filters([
                Filters\SelectFilter::make('status')->options(self::STATUSES),
                Filters\SelectFilter::make('type')->options(self::TYPES),
                Filters\SelectFilter::make('employee')->relationship('employee', 'name'),
                Filters\Filter::make('upcoming')->label('Starts in the next 30 days')
                    ->query(fn (Builder $query) => $query->whereBetween('starts_on', [now()->toDateString(), now()->addDays(30)->toDateString()])),
            ])
            ->actions([
                Action::make('approve')->icon('fa fa-check')->color('success')
                    ->visible(fn (HrLeaveRequest $record) => $record->status === 'pending')
                    ->authorize('update')
                    ->action(fn (HrLeaveRequest $record) => $record->update(['status' => 'approved', 'decided_at' => now()]))
                    ->successMessage('Leave approved.'),
                Action::make('reject')->icon('fa fa-times')->color('danger')
                    ->visible(fn (HrLeaveRequest $record) => $record->status === 'pending')
                    ->authorize('update')
                    ->requiresConfirmation('Reject this leave request?')
                    ->action(fn (HrLeaveRequest $record) => $record->update(['status' => 'rejected', 'decided_at' => now()]))
                    ->successMessage('Leave rejected.'),
            ])
            ->bulkActions([
                BulkAction::make('approve')->label('Approve pending')->icon('fa fa-check')
                    ->authorize('update')
                    ->action(fn (Collection $records) => $records->where('status', 'pending')
                        ->each->update(['status' => 'approved', 'decided_at' => now()]))
                    ->successMessage('Pending requests approved.'),
            ])
            ->defaultSort('starts_on', 'desc');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\Select::make('hr_employee_id')->label('Employee')
                ->relationship('employee', 'name', fn (Builder $query) => $query->where('status', '!=', 'terminated'))
                ->required(),
            Fields\Radio::make('type')->options(self::TYPES)->default('annual')->required(),
            Fields\DatePicker::make('starts_on')->label('From')->required()->columnSpan(6),
            Fields\DatePicker::make('ends_on')->label('To')->required()->rules('after_or_equal:starts_on')->columnSpan(6),
            Fields\Textarea::make('reason')->rows(3),
            Fields\Placeholder::make('status')->onlyOn('edit')
                ->content(fn (?HrLeaveRequest $record) => self::STATUSES[$record?->status] ?? ''),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        return $data + ['status' => 'pending'];
    }

    public function beforeUpdate(array $data, Model $record): array
    {
        // Editing the dates of a decided request sends it back for approval.
        if ($record->status !== 'pending' && ($data['starts_on'] != $record->starts_on->toDateString() || $data['ends_on'] != $record->ends_on->toDateString())) {
            $data['status'] = 'pending';
            $data['decided_at'] = null;
        }

        return $data;
    }
}
