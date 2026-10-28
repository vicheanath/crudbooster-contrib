<?php

namespace App\Admin\Resources\Shop;

use App\Models\ShopCoupon;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * E-commerce example: discount codes. The code is saved upper case (dehydrateStateUsing()), and
 * the discount column combines two attributes (state()).
 */
class CouponResource extends Resource
{
    protected static string $model = ShopCoupon::class;

    protected static ?string $slug = 'shop-coupons';

    protected static ?string $label = 'Coupon';

    protected static ?string $navigationIcon = 'fa fa-ticket';

    protected static ?string $navigationGroup = 'E-commerce (resources)';

    protected static int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'code';

    protected static array $pages = ['index', 'create', 'edit'];

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('code')->searchable()->sortable(),
                Columns\TextColumn::make('discount')->state(fn (ShopCoupon $record) => $record->discount_type === 'percentage'
                    ? rtrim(rtrim((string) $record->discount_value, '0'), '.').'%'
                    : '$'.number_format((float) $record->discount_value, 2)),
                Columns\TextColumn::make('expires_on')->label('Expires')->date('d M Y')->sortable()->placeholder('Never'),
                Columns\BadgeColumn::make('state')->state(fn (ShopCoupon $record) => match (true) {
                    ! $record->is_active => 'Inactive',
                    $record->expires_on?->isPast() => 'Expired',
                    default => 'Active',
                })->colors(['Active' => 'success', 'Expired' => 'secondary', 'Inactive' => 'danger']),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_active')->label('Active'),
                Filters\Filter::make('expired')->query(fn (Builder $query) => $query->whereDate('expires_on', '<', now())),
            ])
            ->bulkActions([
                BulkAction::make('deactivate')->icon('fa fa-pause')
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                    ->successMessage('Coupons deactivated.'),
            ])
            ->defaultSort('code');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('code')->required()->unique()->maxLength(30)->helperText('Saved in upper case.')
                ->dehydrateStateUsing(fn (?string $state) => Str::upper((string) $state)),
            Fields\Radio::make('discount_type')->label('Discount')->options(['percentage' => 'Percentage', 'fixed' => 'Fixed amount'])
                ->default('percentage')->required(),
            Fields\TextInput::make('discount_value')->label('Value')->numeric()->required()->rules('min:0')->columnSpan(6),
            Fields\DatePicker::make('expires_on')->label('Expires on')->helperText('Leave empty for no expiry.')->columnSpan(6),
            Fields\Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }
}
