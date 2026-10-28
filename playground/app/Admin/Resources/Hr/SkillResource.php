<?php

namespace App\Admin\Resources\Hr;

use App\Models\HrSkill;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * HR example: the skills employees pick from (a many-to-many relationship).
 */
class SkillResource extends Resource
{
    protected static string $model = HrSkill::class;

    protected static ?string $slug = 'hr-skills';

    protected static ?string $label = 'Skill';

    protected static ?string $navigationIcon = 'fa fa-graduation-cap';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    // No detail page: a skill is only a name.
    protected static array $pages = ['index', 'create', 'edit'];

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('employees');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable(),
                Columns\TextColumn::make('employees_count')->label('Employees')->sortable()->alignEnd(),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->unique()->maxLength(50),
        ]);
    }
}
