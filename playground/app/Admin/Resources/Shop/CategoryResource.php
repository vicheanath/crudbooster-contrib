<?php

namespace App\Admin\Resources\Shop;

use App\Admin\Resources\ProductResource;
use App\Models\ShopCategory;
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
 * E-commerce example: the same shop_categories table as the classic module, with a product
 * count and a link to the category's products.
 */
class CategoryResource extends Resource
{
    protected static string $model = ShopCategory::class;

    protected static ?string $slug = 'shop-categories';

    protected static ?string $label = 'Category';

    protected static ?string $pluralLabel = 'Categories';

    protected static ?string $navigationIcon = 'fa fa-folder-open';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('products');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable(),
                Columns\TextColumn::make('slug')->searchable(),
                Columns\TextColumn::make('products_count')->label('Products')->sortable()->alignEnd(),
                Columns\BooleanColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Action::make('products')->icon('fa fa-cube')
                    ->url(fn (ShopCategory $record) => ProductResource::url(parameters: ['filters' => ['category' => $record->id]])),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(100)->columnSpan(6),
            Fields\TextInput::make('slug')->unique()->helperText('Leave empty to make one from the name.')->columnSpan(6),
            Fields\Textarea::make('description')->rows(3),
            Fields\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);

        return $data;
    }
}
