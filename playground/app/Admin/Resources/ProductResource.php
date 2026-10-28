<?php

namespace App\Admin\Resources;

use App\Models\ShopProduct;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Forms\Section;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The same shop_products table as the classic AdminShopProductsController, as a resource.
 */
class ProductResource extends Resource
{
    protected static string $model = ShopProduct::class;

    protected static ?string $slug = 'catalog-products';

    protected static ?string $label = 'Product';

    protected static ?string $navigationIcon = 'fa fa-cube';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('image'),
                Columns\TextColumn::make('name')->searchable()->sortable()->url(fn (ShopProduct $record) => static::url('view', $record)),
                Columns\TextColumn::make('sku')->searchable(),
                Columns\TextColumn::make('category.name')->label('Category'),
                Columns\TextColumn::make('price')->money('$')->sortable(),
                Columns\BadgeColumn::make('stock')->sortable()->colors(fn (int $state) => match (true) {
                    $state === 0 => 'danger',
                    $state < 10 => 'warning',
                    default => 'success',
                }),
                Columns\BooleanColumn::make('is_featured')->label('Featured'),
            ])
            ->filters([
                Filters\SelectFilter::make('category')->relationship('category', 'name'),
                Filters\TernaryFilter::make('is_featured')->label('Featured'),
                Filters\Filter::make('out_of_stock')->query(fn ($query) => $query->where('stock', 0)),
            ])
            ->actions([
                Action::make('feature')->icon('fa fa-star')->color('warning')
                    ->visible(fn (ShopProduct $record) => ! $record->is_featured)
                    ->authorize('update')
                    ->action(fn (ShopProduct $record) => $record->update(['is_featured' => true]))
                    ->successMessage('Product featured.'),
            ])
            ->bulkActions([
                BulkAction::make('restock')->label('Restock (+10)')->icon('fa fa-plus')
                    ->requiresConfirmation('Add 10 units to every selected product?')
                    ->action(fn (Collection $records) => $records->each->increment('stock', 10))
                    ->successMessage('Stock updated.'),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(255)->columnSpan(8),
            Fields\TextInput::make('sku')->label('SKU')->required()->unique()->columnSpan(4),
            Fields\Select::make('shop_category_id')->label('Category')->relationship('category', 'name')->required()->columnSpan(6),
            Fields\TextInput::make('price')->numeric()->prefix('$')->required()->columnSpan(3),
            Fields\TextInput::make('stock')->numeric()->default(0)->rules('integer|min:0')->columnSpan(3),
            Fields\Textarea::make('description')->rows(3),
            Section::make('Storefront')->description('How the product appears in the shop.')->schema([
                Fields\FileUpload::make('image')->image()->directory('products')->maxSize(2048)->columnSpan(6),
                Fields\DatePicker::make('available_on')->columnSpan(3),
                Fields\Toggle::make('is_featured')->label('Featured')->columnSpan(3),
            ]),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        $data['sku'] = Str::upper($data['sku']);

        return $data;
    }
}
