<?php

namespace App\Admin\Resources\Shop;

use App\Models\ShopOrder;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Detail;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * E-commerce example: orders with a status workflow (row actions that only show when they
 * apply), a CSV export and a detail page listing the items. The total is kept in sync by
 * OrderItemResource, so it is read-only here.
 */
class OrderResource extends Resource
{
    public const COLORS = ['pending' => 'warning', 'paid' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];

    protected static string $model = ShopOrder::class;

    protected static ?string $slug = 'shop-orders';

    protected static ?string $label = 'Order';

    protected static ?string $navigationIcon = 'fa fa-shopping-cart';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('order_number')->label('Order')->searchable()->sortable()->url(fn (ShopOrder $record) => static::url('view', $record)),
                Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Columns\BadgeColumn::make('status')->sortable()->formatStateUsing(fn (string $state) => ShopOrder::STATUSES[$state] ?? $state)->colors(self::COLORS),
                Columns\TextColumn::make('total')->money('$')->sortable()->alignEnd(),
                Columns\TextColumn::make('placed_at')->label('Placed')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('status')->options(ShopOrder::STATUSES),
                Filters\SelectFilter::make('customer')->relationship('customer', 'name'),
                Filters\Filter::make('this_month')->label('Placed this month')
                    ->query(fn (Builder $query) => $query->where('placed_at', '>=', now()->startOfMonth())),
            ])
            ->actions([
                $this->moveTo('paid', 'Mark paid', 'fa fa-credit-card', 'info', from: ['pending']),
                $this->moveTo('shipped', 'Ship', 'fa fa-truck', 'primary', from: ['paid']),
                $this->moveTo('delivered', 'Delivered', 'fa fa-check', 'success', from: ['shipped']),
                $this->moveTo('cancelled', 'Cancel', 'fa fa-ban', 'danger', from: ['pending', 'paid'])
                    ->requiresConfirmation('Cancel this order?'),
                Action::make('items')->icon('fa fa-list')
                    ->url(fn (ShopOrder $record) => OrderItemResource::url(parameters: ['filters' => ['order' => $record->id]])),
            ])
            ->bulkActions([
                BulkAction::make('ship')->label('Ship paid orders')->icon('fa fa-truck')
                    ->authorize('update')
                    ->action(fn (Collection $records) => $records->where('status', 'paid')->each->update(['status' => 'shipped']))
                    ->successMessage('Paid orders shipped.'),
                BulkAction::make('export')->label('Export CSV')->icon('fa fa-download')
                    ->action(fn (Collection $records) => response()->streamDownload(function () use ($records) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['Order', 'Customer', 'Status', 'Total', 'Placed']);
                        foreach ($records->load('customer') as $order) {
                            fputcsv($out, [$order->order_number, $order->customer?->name, $order->status, $order->total, $order->placed_at?->toDateTimeString()]);
                        }
                        fclose($out);
                    }, 'orders.csv')),
            ])
            ->defaultSort('placed_at', 'desc');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('order_number')->label('Order number')->required()->unique()
                ->default(fn () => 'ORD-'.now()->format('ymd').'-'.random_int(100, 999))->columnSpan(4),
            Fields\Select::make('shop_customer_id')->label('Customer')->relationship('customer', 'name')->required()->columnSpan(8),
            Fields\Select::make('status')->options(ShopOrder::STATUSES)->default('pending')->required()->columnSpan(4),
            Fields\DatePicker::make('placed_at')->label('Placed')->withTime()->default(fn () => now()->format('Y-m-d H:i'))->columnSpan(4),
            Fields\Placeholder::make('total')->content(fn (?ShopOrder $record) => '$'.number_format((float) $record?->total, 2))
                ->helperText('Add items from the order\'s Items button.')->columnSpan(4),
            Fields\Textarea::make('notes')->rows(3),
        ]);
    }

    public function detail(Detail $detail): Detail
    {
        return $detail->entries([
            Columns\TextColumn::make('order_number')->label('Order'),
            Columns\TextColumn::make('customer.name')->label('Customer'),
            Columns\BadgeColumn::make('status')->formatStateUsing(fn (string $state) => ShopOrder::STATUSES[$state] ?? $state)->colors(self::COLORS),
            Columns\TextColumn::make('placed_at')->label('Placed')->dateTime('d M Y H:i'),
            Columns\TextColumn::make('lines')->label('Items')->html()->state(fn (ShopOrder $record) => $record->items->load('product')
                ->map(fn ($item) => e($item->quantity.' × '.$item->product?->name.' @ $'.number_format((float) $item->unit_price, 2)))
                ->implode('<br>') ?: '—'),
            Columns\TextColumn::make('total')->money('$'),
            Columns\TextColumn::make('notes')->placeholder('—'),
        ]);
    }

    /** A row action that moves the order to $status when it is in one of the $from statuses. */
    private function moveTo(string $status, string $label, string $icon, string $color, array $from): Action
    {
        return Action::make($status)->label($label)->icon($icon)->color($color)
            ->visible(fn (ShopOrder $record) => in_array($record->status, $from, true))
            ->authorize('update')
            ->action(fn (ShopOrder $record) => $record->update(['status' => $status]))
            ->successMessage("Order marked $status.");
    }
}
