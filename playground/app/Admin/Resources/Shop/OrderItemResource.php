<?php

namespace App\Admin\Resources\Shop;

use App\Models\ShopOrderItem;
use App\Models\ShopProduct;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * E-commerce example: the lines of an order, opened from an order's Items button (a child
 * resource with no sidebar link). The hooks fill the unit price from the product and keep the
 * order's total up to date.
 */
class OrderItemResource extends Resource
{
    protected static string $model = ShopOrderItem::class;

    protected static ?string $slug = 'shop-order-items';

    protected static ?string $label = 'Order item';

    protected static bool $shouldRegisterNavigation = false;

    protected static array $pages = ['index', 'create', 'edit'];

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('order.order_number')->label('Order')
                    ->url(fn (ShopOrderItem $record) => OrderResource::url('view', $record->shop_order_id)),
                Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Columns\TextColumn::make('quantity')->alignEnd(),
                Columns\TextColumn::make('unit_price')->label('Unit price')->money('$')->alignEnd(),
                Columns\TextColumn::make('line_total')->label('Line total')->money('$')->alignEnd()
                    ->state(fn (ShopOrderItem $record) => $record->quantity * $record->unit_price),
            ])
            ->filters([
                Filters\SelectFilter::make('order')->relationship('order', 'order_number'),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\Select::make('shop_order_id')->label('Order')->relationship('order', 'order_number')
                ->default(fn () => request('filters.order'))->required()->columnSpan(6),
            Fields\Select::make('shop_product_id')->label('Product')->relationship('product', 'name')->required()->columnSpan(6),
            Fields\TextInput::make('quantity')->numeric()->default(1)->rules('integer|min:1')->required()->columnSpan(6),
            Fields\TextInput::make('unit_price')->label('Unit price')->numeric()->prefix('$')
                ->helperText('Leave empty to use the product price.')->columnSpan(6),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        if (blank($data['unit_price'] ?? null)) {
            $data['unit_price'] = ShopProduct::query()->whereKey($data['shop_product_id'])->value('price');
        }

        return $data;
    }

    public function beforeUpdate(array $data, Model $record): array
    {
        return $this->beforeCreate($data);
    }

    public function afterCreate(Model $record): void
    {
        $record->order->recalculateTotal();
    }

    public function afterUpdate(Model $record): void
    {
        $record->order->recalculateTotal();
    }

    public function afterDelete(Model $record): void
    {
        $record->order->recalculateTotal();
    }
}
