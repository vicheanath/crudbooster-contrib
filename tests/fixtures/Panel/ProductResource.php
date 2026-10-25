<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use CrudBooster\Actions\Action;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields\DatePicker;
use CrudBooster\Forms\Fields\FileUpload;
use CrudBooster\Forms\Fields\Select;
use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Fields\Toggle;
use CrudBooster\Forms\Form;
use CrudBooster\Forms\Section;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns\BadgeColumn;
use CrudBooster\Tables\Columns\BooleanColumn;
use CrudBooster\Tables\Columns\TextColumn;
use CrudBooster\Tables\Filters\SelectFilter;
use CrudBooster\Tables\Filters\TernaryFilter;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $navigationIcon = 'fa fa-cube';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $recordTitleAttribute = 'name';

    /** What the lifecycle hooks saw, in order. */
    public static array $calls = [];

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category')->searchable(),
                TextColumn::make('price')->money('$')->sortable(),
                BadgeColumn::make('status')->colors(['published' => 'success', 'draft' => 'secondary']),
                BooleanColumn::make('is_active')->label('Active'),
                TextColumn::make('tags.name')->label('Tags'),
            ])
            ->filters([
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('is_active'),
            ])
            ->actions([
                Action::make('publish')->icon('fa fa-send')->color('success')
                    ->visible(fn (Product $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->authorize('update')
                    ->action(fn (Product $record) => $record->forceFill(['status' => 'published'])->save())
                    ->successMessage('Published.'),
                Action::make('preview')->url(fn (Product $record) => 'https://shop.test/p/'.$record->getKey(), openInNewTab: true),
            ])
            ->bulkActions([
                BulkAction::make('deactivate')
                    ->action(fn (Collection $records) => $records->each(fn ($record) => $record->forceFill(['is_active' => false])->save()))
                    ->successMessage('Deactivated.'),
            ])
            ->defaultSort('name')
            ->paginate(2, [2, 10]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(100)->unique(),
            TextInput::make('price')->numeric()->prefix('$')->columnSpan(6),
            Select::make('category_id')->label('Category')->relationship('category', 'name')->required()->columnSpan(6),
            Section::make('Publishing')->schema([
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                Toggle::make('is_active')->default(true),
                Select::make('tags')->relationship('tags', 'name'),
                DatePicker::make('released_on'),
            ]),
            FileUpload::make('photo')->image()->directory('products')->onlyOn('edit'),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        static::$calls[] = 'beforeCreate';

        return $data + ['code' => strtoupper(substr($data['name'], 0, 3))];
    }

    public function afterCreate(Model $record): void
    {
        static::$calls[] = 'afterCreate:'.$record->tags()->count();
    }

    public function beforeUpdate(array $data, Model $record): array
    {
        static::$calls[] = 'beforeUpdate';

        return $data;
    }

    public function afterDelete(Model $record): void
    {
        static::$calls[] = 'afterDelete:'.$record->name;
    }
}
