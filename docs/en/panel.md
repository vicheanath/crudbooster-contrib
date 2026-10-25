# Resources: Code-First Admin Pages

CRUDBooster now has a second way to build the admin panel: **resources**, plain PHP classes for
your Eloquent models. A resource replaces the Module Generator, the `cms_moduls` row and the
`cbInit()` arrays with one class you write, read and review like any other code.

The classic layer is unchanged. `CBController` modules, `CRUDBooster::*`, the Module Generator,
menus and the API generator keep working. Both layers use the same login, layout, sidebar,
privileges and audit log, so you can keep your existing modules and write new screens as
resources.

| | Classic module | Resource |
| --- | --- | --- |
| Defined in | `cbInit()` arrays + a `cms_moduls` row | A class in `app/Admin/Resources` |
| Data | `DB::table()` rows | Eloquent models, relationships, casts, policies |
| Routes | Created from the database at boot | Created from the class, named (`cb.resources.products.index`) |
| Columns and fields | Arrays with `type` keys | Objects with autocompletion (`TextColumn::make('name')->searchable()`) |
| Customising | `hook_*()` methods, custom views | Methods on the resource, closures, your own column and field classes |
| Permissions | Privileges | A model policy if it has one, otherwise the same privileges |

Namespace: `CrudBooster\`. Source: `core/` in the package.

## Quick start

```bash
php artisan crudbooster:make-resource Product --generate
```

This writes `app/Admin/Resources/ProductResource.php` for `App\Models\Product`. With
`--generate`, the columns and fields come from the model's table. Use `--model=` for another class.
Classes in `app/Admin/Resources` and `app/Admin/Pages` are found automatically, so the page is
ready at `/admin/products` and in the sidebar.

```php
namespace App\Admin\Resources;

use App\Models\Product;
use CrudBooster\Forms\Fields;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns;
use CrudBooster\Tables\Filters;
use CrudBooster\Tables\Table;

class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $navigationIcon = 'fa fa-cube';

    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')->searchable()->sortable(),
                Columns\TextColumn::make('category.name')->label('Category'),
                Columns\TextColumn::make('price')->money('$')->sortable(),
                Columns\BooleanColumn::make('is_active'),
            ])
            ->filters([
                Filters\SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Fields\TextInput::make('name')->required()->maxLength(255),
            Fields\Select::make('category_id')->relationship('category', 'name')->required(),
            Fields\TextInput::make('price')->numeric()->prefix('$'),
            Fields\Toggle::make('is_active')->default(true),
        ]);
    }
}
```

That class gives you a list with search, filters, sorting, pagination and bulk delete, plus
create, edit, detail and delete pages, validation, the sidebar link and audit log entries.

## The resource class

| Property | Default | Purpose |
| --- | --- | --- |
| `$model` | required | The Eloquent model class |
| `$slug` | `products` (kebab plural of the model) | The URL after the admin path, and the module path for privileges |
| `$label`, `$pluralLabel` | from the model name | Shown in titles, buttons and messages |
| `$recordTitleAttribute` | `null` (`Product #12`) | The attribute that names a record |
| `$navigationIcon`, `$navigationGroup`, `$navigationSort` | `fa fa-circle-o`, none, `0` | The sidebar link |
| `$shouldRegisterNavigation` | `true` | `false` hides the sidebar link |
| `$pages` | `['index', 'create', 'view', 'edit']` | Leave a page out to remove it and its routes |

Resources are resolved from the container, so the constructor can take services.

Links: `ProductResource::url()`, `ProductResource::url('create')`,
`ProductResource::url('edit', $product)`,
`ProductResource::url(parameters: ['search' => 'hammer'])`.

### Scoping every page

`modifyQuery()` applies to the list, detail, edit, delete and actions. A record outside the
scope returns a 404:

```php
public function modifyQuery(Builder $query): Builder
{
    return $query->where('shop_id', CRUDBooster::me()->shop_id);
}
```

### Lifecycle hooks

These replace `hook_before_add()` and the other classic hooks. Each save runs in a database
transaction.

```php
public function beforeCreate(array $data): array      // change the attributes before insert
public function afterCreate(Model $record): void      // relationships are already saved
public function beforeUpdate(array $data, Model $record): array
public function afterUpdate(Model $record): void
public function beforeDelete(Model $record): void
public function afterDelete(Model $record): void
```

Eloquent model events and observers run too, because records are saved with `save()` and
removed with `delete()`. `SoftDeletes` works.

## Tables

```php
$table
    ->columns([...])
    ->filters([...])
    ->actions([...])          // buttons on each row
    ->bulkActions([...])      // run on the ticked rows
    ->defaultSort('created_at', 'desc')
    ->paginate(25, [25, 50, 100])
    ->modifyQueryUsing(fn (Builder $query) => $query->where('archived', false))
    ->withoutDefaultActions() // no view / edit / delete buttons
    ->withoutBulkDelete()
    ->emptyStateMessage('No products yet.');
```

### Columns

The name is an attribute or a relationship path. Relationships are eager loaded, so the list does
not run a query per row. A to-many relationship (`tags.name`) shows every value.

| Column | Options |
| --- | --- |
| `TextColumn` | `limit(50)`, `money('$')`, `numeric(2)`, `date('d/m/Y')`, `dateTime()`, `prefix()`, `suffix()`, `html()` |
| `BadgeColumn` | `colors(['published' => 'success', 'draft' => 'secondary'])` or `colors(fn ($state) => ...)` |
| `BooleanColumn` | a check or a cross |
| `ImageColumn` | `disk('s3')`, `size(48)`, `circular()` |

Every column also has `label()`, `searchable()`, `sortable()`, `url(fn ($record) => ...)`,
`state(fn ($record) => ...)` for computed values, `formatStateUsing(fn ($state, $record) => ...)`,
`placeholder()`, `alignEnd()`, `visible()` and `hidden()`. Values are escaped unless the column is
`html()` or the formatter returns an `HtmlString`.

`searchable()` and `sortable()` also take a closure for a custom query:

```php
TextColumn::make('full_name')
    ->state(fn (Customer $record) => "$record->first_name $record->last_name")
    ->searchable(fn (Builder $query, string $search) => $query->where('first_name', 'like', "%$search%")
        ->orWhere('last_name', 'like', "%$search%"));
```

### Filters

```php
SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
SelectFilter::make('category')->relationship('category', 'name'),
TernaryFilter::make('is_active')->labels('Active', 'Inactive'),
Filter::make('expensive')->query(fn (Builder $query) => $query->where('price', '>', 100)),
```

Filters read `?filters[name]=value`, so a filtered list is a link you can share:
`ProductResource::url(parameters: ['filters' => ['status' => 'draft']])`.

### Row and bulk actions

```php
->actions([
    Action::make('publish')->icon('fa fa-send')->color('success')
        ->visible(fn (Post $record) => ! $record->published_at)
        ->authorize('update')                      // only for admins who may update
        ->requiresConfirmation('Publish this post?')
        ->action(fn (Post $record) => $record->publish())
        ->successMessage('Post published.'),

    Action::make('preview')->icon('fa fa-external-link')
        ->url(fn (Post $record) => route('posts.show', $record), openInNewTab: true),
])
->bulkActions([
    BulkAction::make('archive')
        ->action(fn (Collection $records) => $records->each->archive()),
])
```

Closures are called through the container. They can ask for `$record`, `$records`, `$data`
(the request input) or any service. If an action returns a response (a download, a redirect),
that response is sent.

A hidden action cannot be posted either: the server checks `visible()` and `authorize()` again.

## Forms

```php
$form->schema([
    TextInput::make('name')->required()->maxLength(255)->columnSpan(8),
    TextInput::make('sku')->unique()->columnSpan(4),
    Section::make('Pricing')->description('Shown in the shop')->schema([
        TextInput::make('price')->numeric()->prefix('$')->columnSpan(6),
        Toggle::make('on_sale')->columnSpan(6),
    ]),
]);
```

| Field | Notes |
| --- | --- |
| `TextInput` | `email()`, `numeric()`, `tel()`, `url()`, `type('color')`, `password()`, `maxLength()`, `prefix()`, `suffix()` |
| `Textarea` | `rows(6)` |
| `Select` | `options([...])`, `relationship('category', 'name')`, `multiple()` |
| `Radio`, `CheckboxList` | Same options as `Select` |
| `Toggle` | A boolean switch; unchecked saves `false` |
| `DatePicker` | `withTime()`, `timeOnly()` |
| `FileUpload` | `image()`, `disk()`, `directory()`, `acceptedFileTypes(['pdf'])`, `maxSize(2048)` |
| `Hidden` | |
| `Placeholder` | Read-only text: `content(fn ($record) => ...)` |
| `ViewField` | Your own Blade view: `view('admin.fields.map')` |

Every field also has `label()`, `required()`, `rules('integer|min:0')`, `default()`,
`helperText()`, `placeholder()`, `disabled()`, `columnSpan(1-12)`, `onlyOn('create')`,
`visible()`, `hidden()`, `extraAttributes([...])`, `dehydrateStateUsing(fn ($state) => ...)`
(changes the value before saving) and `formatStateUsing(fn ($state) => ...)` (changes it before
display).

What happens on save:
- **Only the fields in the form are saved.** The form is the whitelist, so you don't need
  `$fillable` for the panel, and posting other attributes does nothing.
- **Validation comes from the fields.** `required()`, the field type (`email`, `numeric`,
  `date`), `maxLength()` and `unique()` (which ignores the record being edited) all add rules.
  `Select`, `Radio` and `CheckboxList` only accept their options or records that exist in the
  related table. Messages use the field labels.
- **Passwords:** `password()` hashes the value and never shows the stored hash. An empty value
  on edit keeps the current password.
- **Relationships:** a `Select` on a `BelongsToMany` relationship is synced after the record is
  saved: `Select::make('tags')->relationship('tags', 'name')`.
- **Files:** `FileUpload` stores the file on the `crudbooster.PANEL.disk` disk (`public` by
  default) under a random name and saves its path. It accepts images with `image()`, the
  extensions you pass to `acceptedFileTypes()`, or otherwise those in `crudbooster.UPLOAD_TYPES`.
  Both the file name and the detected content type are checked, and the stored file gets the
  extension of its content. On edit, an empty input keeps the current file, and the Remove
  checkbox clears it. Replaced files stay on the disk.

The value of a closure option (`visible()`, `required()`, `disabled()`, `options()`) can depend on
`$record` (null on create) and `$operation` (`create` or `edit`):

```php
TextInput::make('slug')->disabled(fn (string $operation) => $operation === 'edit'),
```

## Detail page

By default the detail page shows the table's columns. To choose its entries, override `detail()`:

```php
public function detail(Detail $detail): Detail
{
    return $detail->entries([
        TextColumn::make('name'),
        TextColumn::make('description')->html(),
        TextColumn::make('created_at')->dateTime(),
    ]);
}
```

## Permissions

`$resource->can($ability, $record)` decides what an admin may do (`viewAny`, `view`, `create`,
`update`, `delete`). It checks in this order:

1. **A Laravel policy for the model**, if one exists. The policy gets the logged-in admin's
   `CmsUser` model and applies to superadmins too.
2. **Superadmins** may do anything.
3. **Other admins** get the privilege matrix of the module whose path is the resource's slug:
   view = `is_read`, create = `is_create`, update = `is_edit`, delete = `is_delete`.

Without a policy, a non-superadmin has no access until a privilege grants it. To list your
resources and pages on **Privileges > Edit**, run:

```bash
php artisan crudbooster:sync-panel
```

This adds a module row (with no controller) for each resource and page, and is safe to run
again. Buttons, row actions, sidebar links and routes follow the same rules. To use another
system, such as spatie/permission, bind your own `CrudBooster\Auth\Authorizer`, or override
`can()` on a resource.

## Commands

| Command | |
| --- | --- |
| `crudbooster:make-resource Product [--model=] [--generate] [--force]` | A resource; `--generate` reads the columns and fields from the table |
| `crudbooster:make-page SalesReport [--force]` | A page class and its view |
| `crudbooster:sync-panel` | Module rows for privileges |

## Configuration

In `config/crudbooster.php`. Existing published configs get the defaults without changes:

```php
'PANEL' => [
    'discover' => ['App\\Admin\\Resources' => app_path('Admin/Resources'), 'App\\Admin\\Pages' => app_path('Admin/Pages')],
    'disk' => 'public',
    'middleware' => [],          // added to every panel route, after "web" and the login check
],
```

To register classes that live elsewhere, call this from a service provider's `boot()`:

```php
use CrudBooster\Facades\Panel;

Panel::resources([ProductResource::class])->pages([SalesReport::class]);
```

Next: [custom pages](./panel-pages.md), [extending the panel](./panel-extending.md) and
[themes](./panel-themes.md) (AdminLTE, or Tailwind CSS with React inputs).
