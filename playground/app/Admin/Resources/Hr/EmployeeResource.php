<?php

namespace App\Admin\Resources\Hr;

use App\Models\HrEmployee;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Forms\Section;
use CrudBooster\Resources\Detail;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * HR example: employees with a department, a manager (a relationship to the same table),
 * skills (many-to-many), a photo and a CSV export.
 */
class EmployeeResource extends Resource
{
    public const TYPES = ['full_time' => 'Full time', 'part_time' => 'Part time', 'contract' => 'Contract', 'intern' => 'Intern'];

    public const STATUSES = ['active' => 'Active', 'on_leave' => 'On leave', 'terminated' => 'Terminated'];

    protected static string $model = HrEmployee::class;

    protected static ?string $slug = 'hr-employees';

    protected static ?string $label = 'Employee';

    protected static ?string $navigationIcon = 'fa fa-user';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('photo')->circular()->size(32),
                Columns\TextColumn::make('employee_number')->label('No.')->searchable()->sortable(),
                Columns\TextColumn::make('name')->searchable()->sortable()->url(fn (HrEmployee $record) => static::url('view', $record)),
                Columns\TextColumn::make('job_title')->searchable(),
                Columns\TextColumn::make('department.name')->label('Department'),
                Columns\TextColumn::make('manager.name')->label('Manager')->placeholder('—'),
                Columns\BadgeColumn::make('employment_type')->label('Type')
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state)
                    ->colors(['full_time' => 'primary', 'part_time' => 'info', 'contract' => 'warning', 'intern' => 'secondary']),
                Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->colors(['active' => 'success', 'on_leave' => 'warning', 'terminated' => 'danger']),
                Columns\TextColumn::make('hired_on')->date('d M Y')->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('department')->relationship('department', 'name'),
                Filters\SelectFilter::make('employment_type')->label('Type')->options(self::TYPES),
                Filters\SelectFilter::make('status')->options(self::STATUSES),
                Filters\TernaryFilter::make('is_remote')->label('Remote'),
            ])
            ->actions([
                Action::make('terminate')->icon('fa fa-user-times')->color('danger')
                    ->visible(fn (HrEmployee $record) => $record->status !== 'terminated')
                    ->authorize('update')
                    ->requiresConfirmation('Mark this employee as terminated?')
                    ->action(fn (HrEmployee $record) => $record->update(['status' => 'terminated']))
                    ->successMessage('Employee terminated.'),
            ])
            ->bulkActions([
                BulkAction::make('export')->label('Export CSV')->icon('fa fa-download')
                    ->action(fn (Collection $records) => response()->streamDownload(function () use ($records) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['Number', 'Name', 'Email', 'Department', 'Job title', 'Hired on']);
                        foreach ($records->load('department') as $employee) {
                            fputcsv($out, [$employee->employee_number, $employee->name, $employee->email,
                                $employee->department?->name, $employee->job_title, $employee->hired_on->toDateString()]);
                        }
                        fclose($out);
                    }, 'employees.csv')),
            ])
            ->defaultSort('name')
            ->paginate(25, [25, 50, 100]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Person')->schema([
                Fields\TextInput::make('name')->required()->maxLength(100)->columnSpan(6),
                Fields\TextInput::make('employee_number')->label('Employee number')->required()->unique()
                    ->default(fn () => 'EMP-'.str_pad((string) (HrEmployee::query()->max('id') + 1), 4, '0', STR_PAD_LEFT))
                    ->columnSpan(6),
                Fields\TextInput::make('email')->email()->required()->unique()->columnSpan(6),
                Fields\TextInput::make('phone')->tel()->columnSpan(6),
                Fields\FileUpload::make('photo')->image()->directory('employees')->maxSize(1024),
            ]),
            Section::make('Job')->schema([
                Fields\TextInput::make('job_title')->required()->columnSpan(6),
                Fields\Select::make('hr_department_id')->label('Department')
                    ->relationship('department', 'name', fn (Builder $query) => $query->where('is_active', true))
                    ->required()->columnSpan(6),
                Fields\Select::make('manager_id')->label('Manager')->relationship('manager', 'name')->columnSpan(6),
                Fields\DatePicker::make('hired_on')->label('Hired on')->required()->default(fn () => now()->toDateString())->columnSpan(6),
                Fields\Radio::make('employment_type')->label('Type')->options(self::TYPES)->default('full_time')->required(),
                Fields\Select::make('status')->options(self::STATUSES)->default('active')->required()->columnSpan(6),
                Fields\TextInput::make('salary')->numeric()->prefix('$')->suffix('/ year')->rules('min:0')->columnSpan(6),
                Fields\Toggle::make('is_remote')->label('Works remotely'),
                Fields\Select::make('skills')->relationship('skills', 'name')->multiple(),
            ]),
            Fields\Textarea::make('notes')->rows(3)->onlyOn('edit'),
        ]);
    }

    public function detail(Detail $detail): Detail
    {
        return $detail->entries([
            Columns\ImageColumn::make('photo')->size(96),
            Columns\TextColumn::make('employee_number')->label('Employee number'),
            Columns\TextColumn::make('name'),
            Columns\TextColumn::make('email'),
            Columns\TextColumn::make('phone')->placeholder('—'),
            Columns\TextColumn::make('job_title'),
            Columns\TextColumn::make('department.name')->label('Department'),
            Columns\TextColumn::make('manager.name')->label('Manager')->placeholder('—'),
            Columns\TextColumn::make('reports.name')->label('Direct reports')->placeholder('—'),
            Columns\TextColumn::make('skills.name')->label('Skills')->placeholder('—'),
            Columns\TextColumn::make('salary')->money('$'),
            Columns\BooleanColumn::make('is_remote')->label('Remote'),
            Columns\TextColumn::make('hired_on')->date('d M Y'),
            Columns\TextColumn::make('notes')->placeholder('—'),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        $data['email'] = strtolower($data['email']);

        return $data;
    }
}
