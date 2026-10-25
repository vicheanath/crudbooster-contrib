<?php

namespace App\Admin\Resources;

use App\Models\ShopReview;
use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Support\Collection;

/**
 * Moderation: no create page, reviews come from customers.
 */
class ReviewResource extends Resource
{
    protected static string $model = ShopReview::class;

    protected static ?string $slug = 'catalog-reviews';

    protected static ?string $label = 'Review';

    protected static ?string $navigationIcon = 'fa fa-star-half-o';

    protected static ?string $navigationGroup = 'Catalog (resources)';

    protected static int $navigationSort = 1;

    protected static array $pages = ['index', 'view', 'edit'];

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('product.name')->label('Product')->searchable(),
                Columns\TextColumn::make('customer.name')->label('Customer')->searchable(),
                Columns\BadgeColumn::make('rating')->sortable()
                    ->formatStateUsing(fn (int $state) => str_repeat('★', $state))
                    ->colors(fn (int $state) => $state >= 4 ? 'success' : ($state <= 2 ? 'danger' : 'warning')),
                Columns\TextColumn::make('comment')->limit(60),
                Columns\BooleanColumn::make('is_published')->label('Published'),
                Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_published')->label('Published'),
                Filters\SelectFilter::make('rating')->options([5 => '5 ★', 4 => '4 ★', 3 => '3 ★', 2 => '2 ★', 1 => '1 ★']),
            ])
            ->actions([
                Action::make('publish')->icon('fa fa-check')->color('success')
                    ->visible(fn (ShopReview $record) => ! $record->is_published)
                    ->action(fn (ShopReview $record) => $record->update(['is_published' => true]))
                    ->successMessage('Review published.'),
            ])
            ->bulkActions([
                BulkAction::make('publish')->icon('fa fa-check')
                    ->action(fn (Collection $records) => $records->each->update(['is_published' => true]))
                    ->successMessage('Reviews published.'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\Placeholder::make('product')->content(fn (ShopReview $record) => $record->product?->name),
            Fields\Radio::make('rating')->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])->required(),
            Fields\Textarea::make('comment'),
            Fields\Toggle::make('is_published')->label('Published'),
        ]);
    }
}
