<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Table;

/**
 * Only a create page: a form without a list.
 */
class FeedbackResource extends Resource
{
    protected static string $model = Tag::class;

    protected static ?string $slug = 'feedback';

    protected static array $pages = ['create'];

    public function table(Table $table): Table
    {
        return $table;
    }

    public function form(Form $form): Form
    {
        return $form->schema([TextInput::make('name')->required()]);
    }
}
