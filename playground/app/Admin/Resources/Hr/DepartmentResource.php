<?php

namespace App\Admin\Resources\Hr;

use App\Models\HrDepartment;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * HR example: departments, with a headcount and a link to their employees.
 */
class DepartmentResource extends Resource
{
    protected static string $model = HrDepartment::class;

    protected static ?string $slug = 'hr-departments';

    protected static ?string $label = 'Department';

    protected static ?string $navigationIcon = 'fa fa-sitemap';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('employees');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('code')->searchable()->sortable(),
                Columns\TextColumn::make('name')->searchable()->sortable(),
                Columns\TextColumn::make('employees_count')->label('Headcount')->sortable()->alignEnd(),
                Columns\BooleanColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                // A "sub module": opens the employees list filtered by this department.
                Action::make('employees')->icon('fa fa-users')
                    ->url(fn (HrDepartment $record) => EmployeeResource::url(parameters: ['filters' => ['department' => $record->id]])),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(100)->columnSpan(8),
            Fields\TextInput::make('code')->required()->unique()->maxLength(10)->helperText('Short code, e.g. ENG.')->columnSpan(4)
                ->dehydrateStateUsing(fn (?string $state) => Str::upper((string) $state)),
            Fields\Textarea::make('description')->rows(3),
            Fields\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}
