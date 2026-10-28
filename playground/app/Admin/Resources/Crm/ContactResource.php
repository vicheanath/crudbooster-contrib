<?php

namespace App\Admin\Resources\Crm;

use App\Models\CrmContact;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use CrudBooster\Actions\BulkAction;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;
use Illuminate\Support\Collection;

/**
 * CRM example: contacts with a company, tags (checkboxes on a pivot table) and an owner, who
 * defaults to the admin creating the contact (a service injected into the resource).
 */
class ContactResource extends Resource
{
    protected static string $model = CrmContact::class;

    protected static ?string $slug = 'crm-contacts';

    protected static ?string $label = 'Contact';

    protected static ?string $navigationIcon = 'fa fa-book';

    protected static ?string $navigationGroup = 'CRM';

    protected static int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public function __construct(private readonly AuthenticationServiceInterface $auth)
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable()->url(fn (CrmContact $record) => static::url('view', $record)),
                Columns\TextColumn::make('job_title')->placeholder('—'),
                Columns\TextColumn::make('company.name')->label('Company')->placeholder('—'),
                Columns\TextColumn::make('email')->searchable()->url(fn (CrmContact $record) => 'mailto:'.$record->email),
                Columns\TextColumn::make('phone')->placeholder('—'),
                Columns\TextColumn::make('tags.name')->label('Tags')->placeholder('—'),
                Columns\TextColumn::make('owner.name')->label('Owner')->placeholder('—'),
                Columns\BooleanColumn::make('is_subscribed')->label('Newsletter'),
            ])
            ->filters([
                Filters\SelectFilter::make('company')->relationship('company', 'name'),
                Filters\SelectFilter::make('owner')->relationship('owner', 'name'),
                Filters\TernaryFilter::make('is_subscribed')->label('Newsletter')->labels('Subscribed', 'Not subscribed'),
            ])
            ->bulkActions([
                BulkAction::make('subscribe')->icon('fa fa-envelope')
                    ->action(fn (Collection $records) => $records->each->update(['is_subscribed' => true]))
                    ->successMessage('Contacts subscribed to the newsletter.'),
                BulkAction::make('unsubscribe')->icon('fa fa-envelope-o')
                    ->action(fn (Collection $records) => $records->each->update(['is_subscribed' => false]))
                    ->successMessage('Contacts unsubscribed.'),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(100)->columnSpan(6),
            Fields\TextInput::make('job_title')->columnSpan(6),
            Fields\TextInput::make('email')->email()->required()->unique()->columnSpan(6),
            Fields\TextInput::make('phone')->tel()->columnSpan(6),
            Fields\Select::make('crm_company_id')->label('Company')->relationship('company', 'name')->columnSpan(6),
            Fields\Select::make('owner_id')->label('Owner')->relationship('owner', 'name')
                ->default(fn () => $this->auth->currentUserId())->columnSpan(6),
            Fields\CheckboxList::make('tags')->relationship('tags', 'name'),
            Fields\Toggle::make('is_subscribed')->label('Subscribed to the newsletter'),
        ]);
    }
}
