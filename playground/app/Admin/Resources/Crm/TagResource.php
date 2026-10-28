<?php

namespace App\Admin\Resources\Crm;

use App\Models\CrmTag;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * CRM example: tags for contacts (a many-to-many relationship shown as checkboxes).
 */
class TagResource extends Resource
{
    protected static string $model = CrmTag::class;

    protected static ?string $slug = 'crm-tags';

    protected static ?string $label = 'Tag';

    protected static ?string $navigationIcon = 'fa fa-tags';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    protected static array $pages = ['index', 'create', 'edit'];

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('contacts');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable(),
                Columns\TextColumn::make('contacts_count')->label('Contacts')->sortable()->alignEnd(),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->unique()->maxLength(30),
        ]);
    }
}
