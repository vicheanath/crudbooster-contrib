<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns\TextColumn;
use CrudBooster\Tables\Table;

/**
 * A read-only resource: list and detail pages only.
 */
class CategoryResource extends Resource
{
    protected static string $model = Category::class;

    protected static ?string $slug = 'product-categories';

    protected static array $pages = ['index', 'view'];

    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([TextInput::make('name')]);
    }
}
