<?php

namespace App\Admin\Resources\Shop;

use App\Models\ShopCustomer;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * E-commerce example: customers with their order count and lifetime spend (withSum()).
 */
class CustomerResource extends Resource
{
    public const STATUSES = ['active' => 'Active', 'vip' => 'VIP', 'blocked' => 'Blocked'];

    protected static string $model = ShopCustomer::class;

    protected static ?string $slug = 'shop-customers';

    protected static ?string $label = 'Customer';

    protected static ?string $navigationIcon = 'fa fa-user';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public function modifyQuery(Builder $query): Builder
    {
        return $query->withCount('orders')
            ->withSum(['orders as spent' => fn (Builder $orders) => $orders->where('status', '!=', 'cancelled')], 'total');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable()->url(fn (ShopCustomer $record) => static::url('view', $record)),
                Columns\TextColumn::make('email')->searchable(),
                Columns\TextColumn::make('phone')->placeholder('—'),
                Columns\BadgeColumn::make('status')->formatStateUsing(fn (string $state) => self::STATUSES[$state] ?? $state)
                    ->colors(['active' => 'success', 'vip' => 'primary', 'blocked' => 'danger']),
                Columns\TextColumn::make('orders_count')->label('Orders')->sortable()->alignEnd(),
                Columns\TextColumn::make('spent')->money('$')->sortable()->alignEnd(),
            ])
            ->filters([
                Filters\SelectFilter::make('status')->options(self::STATUSES),
                Filters\Filter::make('without_orders')->label('No orders yet')->query(fn (Builder $query) => $query->doesntHave('orders')),
            ])
            ->actions([
                Action::make('orders')->icon('fa fa-shopping-cart')
                    ->url(fn (ShopCustomer $record) => OrderResource::url(parameters: ['filters' => ['customer' => $record->id]])),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(100)->columnSpan(6),
            Fields\TextInput::make('email')->email()->required()->unique()->columnSpan(6),
            Fields\TextInput::make('phone')->tel()->columnSpan(6),
            Fields\Select::make('status')->options(self::STATUSES)->default('active')->required()->columnSpan(6),
            Fields\Textarea::make('address')->rows(3),
        ]);
    }
}
