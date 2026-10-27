# Migrating A Module Step By Step

This page converts a classic module into a [resource](./panel.md), one small example at a time. Each
example shows the classic code first, then the code that replaces it. For the full list of
deprecated APIs, see [Deprecated APIs and How To Replace Them](./deprecations.md).

You can migrate one module at a time. Classic modules and resources share the login, sidebar,
privileges and audit log, so both kinds of screens work side by side.

## The whole module in six steps

We migrate this Products module. It lists products with their category, and has a form with a
name, a category, a price and a photo.

```php
// app/Http/Controllers/AdminProductsController.php (classic)
class AdminProductsController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function cbInit()
    {
        $this->table = 'products';
        $this->title_field = 'name';

        $this->col[] = ['label' => 'Name', 'name' => 'name'];
        $this->col[] = ['label' => 'Category', 'name' => 'category_id', 'join' => 'categories,name'];
        $this->col[] = ['label' => 'Price', 'name' => 'price'];

        $this->form[] = ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'validation' => 'required|max:255'];
        $this->form[] = ['label' => 'Category', 'name' => 'category_id', 'type' => 'select', 'datatable' => 'categories,name'];
        $this->form[] = ['label' => 'Price', 'name' => 'price', 'type' => 'money'];
        $this->form[] = ['label' => 'Photo', 'name' => 'photo', 'type' => 'upload'];
    }
}
```

### Step 1: Add an Eloquent model

Resources work with models, not table names. Add a model, and a relationship for each `join` or
`datatable`:

```php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

`Category` is a model for the `categories` table. You don't need `$fillable`: the resource form
decides which fields are saved.

### Step 2: Generate the resource

```bash
php artisan crudbooster:make-resource Product --generate
```

This writes `app/Admin/Resources/ProductResource.php`, with columns and fields guessed from the table.

### Step 3: Move the columns and fields

Turn each `$this->col[]` into a column and each `$this->form[]` into a field:

```php
// app/Admin/Resources/ProductResource.php
namespace App\Admin\Resources;

use App\Models\Product;
use CrudBooster\Forms\Fields\FileUpload;
use CrudBooster\Forms\Fields\Select;
use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns\TextColumn;
use CrudBooster\Tables\Table;

class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';   // was $this->title_field

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('category.name')->label('Category'),   // was 'join'
            TextColumn::make('price')->money('$'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),    // was 'validation'
            Select::make('category_id')->relationship('category', 'name'),
            TextInput::make('price')->numeric()->prefix('$'),
            FileUpload::make('photo')->image(),
        ]);
    }
}
```

### Step 4: Move the hooks

Each `hook_*()` method becomes a method on the resource. See [Hooks](#hooks) below.

### Step 5: Keep the URL and the privileges

The resource's slug is `products`, the same path as the classic module, so links and bookmarks keep
working. To keep the privileges you already granted, detach the classic controller from its module
row, then sync. The row (and its privileges) now belongs to the resource:

```sql
UPDATE cms_moduls SET controller = NULL WHERE path = 'products';
```

```bash
php artisan crudbooster:sync-panel
```

Menus of type **Module** link by path, so they open the resource. The resource also adds its own
sidebar link. Keep one of them: delete the menu in **Menu Management**, or hide the resource's link:

```php
protected static bool $shouldRegisterNavigation = false;
```

### Step 6: Delete the classic controller

Delete `app/Http/Controllers/AdminProductsController.php`, open `/admin/products` and try listing,
creating, editing and deleting a product.

::: tip Old uploads
Files uploaded by the classic module are stored as `uploads/...` and served by CRUDBooster, while
`FileUpload` stores new files on the `public` disk. To show the old files, pass a full URL:

```php
ImageColumn::make('photo')->state(fn (Product $record) => $record->photo ? asset($record->photo) : null),
```
:::

## Columns

### A plain column

```php
// Before
$this->col[] = ['label' => 'Product Name', 'name' => 'name'];

// After
TextColumn::make('name')->label('Product Name')->searchable()->sortable(),
```

### A joined column

```php
// Before
$this->col[] = ['label' => 'Category', 'name' => 'category_id', 'join' => 'categories,name'];

// After: needs a category() relationship on the model
TextColumn::make('category.name')->label('Category'),
```

### Number format and callbacks

```php
// Before
$this->col[] = ['label' => 'Price', 'name' => 'price', 'callback_php' => 'number_format($row->price)'];
$this->col[] = ['label' => 'Total', 'name' => 'id', 'callback' => function ($row) {
    return $row->price * $row->stock;
}];

// After
TextColumn::make('price')->numeric(0),
TextColumn::make('total')->state(fn (Product $record) => $record->price * $record->stock),
```

### Image, short text and HTML

```php
// Before
$this->col[] = ['label' => 'Photo', 'name' => 'photo', 'image' => true];
$this->col[] = ['label' => 'Description', 'name' => 'description', 'str_limit' => 50];

// After
ImageColumn::make('photo'),
TextColumn::make('description')->limit(50),
```

### Coloured status

```php
// Before: $this->table_row_color or a callback that returns a label
$this->col[] = ['label' => 'Status', 'name' => 'status', 'callback' => function ($row) {
    return $row->status == 'active' ? '<span class="label label-success">Active</span>' : $row->status;
}];

// After
BadgeColumn::make('status')->colors(['active' => 'success', 'draft' => 'secondary']),
```

### A subquery column

```php
// Before
$this->col[] = ['label' => 'Orders', 'name' => '(select count(*) from orders where orders.product_id = products.id) as total_orders'];

// After: count a hasMany relationship in modifyQuery()
public function modifyQuery(Builder $query): Builder
{
    return $query->withCount('orders');
}

TextColumn::make('orders_count')->label('Orders'),
```

## The list query

```php
// Before
public function hook_query_index(&$query)
{
    $query->where('products.is_active', 1);
}

// After: the list only
$table->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true));

// After: every page (list, detail, edit, delete). Other records return a 404.
public function modifyQuery(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

Default order and page size:

```php
// Before
$this->orderby = 'name,asc';
$this->limit = 25;

// After
$table->defaultSort('name', 'asc')->paginate(25);
```

## Filters

```php
// Before: the filter button of each column ($this->button_filter = true)

// After
use CrudBooster\Tables\Filters\SelectFilter;
use CrudBooster\Tables\Filters\TernaryFilter;

$table->filters([
    SelectFilter::make('category')->relationship('category', 'name'),
    SelectFilter::make('status')->options(['active' => 'Active', 'draft' => 'Draft']),
    TernaryFilter::make('is_active')->labels('Active', 'Inactive'),
]);
```

## Form fields

### Text, email, number, password, textarea

```php
// Before
$this->form[] = ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'validation' => 'required|min:3'];
$this->form[] = ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'validation' => 'required|email'];
$this->form[] = ['label' => 'Stock', 'name' => 'stock', 'type' => 'number'];
$this->form[] = ['label' => 'Password', 'name' => 'password', 'type' => 'password'];
$this->form[] = ['label' => 'Notes', 'name' => 'notes', 'type' => 'textarea'];

// After
TextInput::make('name')->required()->rules('min:3'),
TextInput::make('email')->email()->required(),
TextInput::make('stock')->numeric(),
TextInput::make('password')->password(),   // hashed; empty on edit keeps the old one
Textarea::make('notes'),
```

### Select from a table

```php
// Before
$this->form[] = ['label' => 'Category', 'name' => 'category_id', 'type' => 'select2', 'datatable' => 'categories,name'];

// After: needs a category() relationship on the model
Select::make('category_id')->relationship('category', 'name')->required(),
```

### Select from a fixed list

```php
// Before
$this->form[] = ['label' => 'Status', 'name' => 'status', 'type' => 'select', 'dataenum' => 'active|Active;draft|Draft'];

// After
Select::make('status')->options(['active' => 'Active', 'draft' => 'Draft']),
Radio::make('status')->options(['active' => 'Active', 'draft' => 'Draft']),   // or as radio buttons
```

### Many to many

```php
// Before
$this->form[] = ['label' => 'Tags', 'name' => 'tags', 'type' => 'select2', 'datatable' => 'tags,name',
    'relationship_table' => 'product_tag'];

// After: needs a tags() BelongsToMany relationship on the model
Select::make('tags')->relationship('tags', 'name')->multiple(),
CheckboxList::make('tags')->relationship('tags', 'name'),   // or as checkboxes
```

### Date, upload, yes/no, hidden

```php
// Before
$this->form[] = ['label' => 'Published', 'name' => 'published_at', 'type' => 'datetime'];
$this->form[] = ['label' => 'Photo', 'name' => 'photo', 'type' => 'upload', 'validation' => 'image|max:2048'];
$this->form[] = ['label' => 'Active', 'name' => 'is_active', 'type' => 'radio', 'dataenum' => '1|Yes;0|No'];
$this->form[] = ['label' => 'Shop', 'name' => 'shop_id', 'type' => 'hidden', 'value' => 1];

// After
DatePicker::make('published_at')->withTime(),
FileUpload::make('photo')->image()->maxSize(2048),
Toggle::make('is_active')->default(true),
Hidden::make('shop_id')->default(1),
```

### Sections and widths

```php
// Before
$this->form[] = ['label' => 'Pricing', 'name' => 'pricing', 'type' => 'header'];
$this->form[] = ['label' => 'Price', 'name' => 'price', 'type' => 'money', 'width' => 'col-sm-6'];

// After
use CrudBooster\Forms\Section;

Section::make('Pricing')->schema([
    TextInput::make('price')->numeric()->prefix('$')->columnSpan(6),
    TextInput::make('cost')->numeric()->prefix('$')->columnSpan(6),
]),
```

## Hooks

```php
// Before
public function hook_before_add(&$postdata)
{
    $postdata['created_by'] = CRUDBooster::myId();
}

public function hook_after_add($id)
{
    CRUDBooster::sendNotification(['content' => 'New product', 'to' => CRUDBooster::adminPath('products'), 'id_cms_users' => [1]]);
}

// After: the resource is built by the container, so it can ask for services
public function __construct(
    private AuthenticationServiceInterface $auth,
    private NotificationSenderInterface $notifications,
) {
}

public function beforeCreate(array $data): array
{
    $data['created_by'] = $this->auth->currentUserId();

    return $data;   // return the data instead of changing it by reference
}

public function afterCreate(Model $record): void
{
    $this->notifications->send('New product', static::url('view', $record), [1]);
}
```

Both interfaces are in `crocodicstudio\crudbooster\contracts` (`Security\` and `Notifications\`).
The other hooks:

| Before | After |
| --- | --- |
| `hook_before_edit(&$postdata, $id)` | `beforeUpdate(array $data, Model $record): array` |
| `hook_after_edit($id)` | `afterUpdate(Model $record): void` |
| `hook_before_delete($id)` | `beforeDelete(Model $record): void` |
| `hook_after_delete($id)` | `afterDelete(Model $record): void` |

You get the record itself instead of its id, so `DB::table('products')->find($id)` is not needed.

## Buttons

### Hide buttons

```php
// Before
$this->button_add = false;
$this->button_edit = false;
$this->button_bulk_action = false;

// After: leave pages out of $pages to remove them and their routes
protected static array $pages = ['index', 'view'];

public function table(Table $table): Table
{
    return $table->columns([...])->withoutBulkDelete();
}
```

### A button on each row

```php
// Before
$this->addaction[] = ['label' => 'Publish', 'url' => CRUDBooster::mainpath('publish/[id]'),
    'icon' => 'fa fa-send', 'color' => 'success', 'showIf' => '[status] == "draft"'];

public function getPublish($id)
{
    DB::table('products')->where('id', $id)->update(['status' => 'published']);
    CRUDBooster::redirect(CRUDBooster::mainpath(), 'Published!', 'success');
}

// After: no extra route or method
use CrudBooster\Actions\Action;

$table->actions([
    Action::make('publish')->icon('fa fa-send')->color('success')
        ->visible(fn (Product $record) => $record->status === 'draft')
        ->requiresConfirmation('Publish this product?')
        ->action(fn (Product $record) => $record->update(['status' => 'published']))
        ->successMessage('Published!'),
]);
```

### A button for the ticked rows

```php
// Before
$this->button_selected[] = ['label' => 'Archive', 'icon' => 'fa fa-archive', 'name' => 'archive'];

public function actionButtonSelected($id_selected, $button_name)
{
    if ($button_name == 'archive') {
        DB::table('products')->whereIn('id', $id_selected)->update(['archived' => 1]);
    }
}

// After
use CrudBooster\Actions\BulkAction;
use Illuminate\Support\Collection;

$table->bulkActions([
    BulkAction::make('archive')->icon('fa fa-archive')
        ->action(fn (Collection $records) => $records->each->update(['archived' => true]))
        ->successMessage('Archived.'),
]);
```

### Export

```php
// Before
$this->button_export = true;

// After: a bulk action that returns a download
BulkAction::make('export')->icon('fa fa-download')
    ->action(fn (Collection $records) => response()->streamDownload(function () use ($records) {
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Name', 'Price']);
        foreach ($records as $product) {
            fputcsv($out, [$product->name, $product->price]);
        }
        fclose($out);
    }, 'products.csv')),
```

## Sub modules (parent and child)

```php
// Before: in the Categories module
$this->sub_module[] = ['label' => 'Products', 'path' => 'products', 'parent_columns' => 'name',
    'foreign_key' => 'category_id', 'button_color' => 'success', 'button_icon' => 'fa fa-bars'];

// After: in CategoryResource, a row action that opens the products of that category
Action::make('products')->icon('fa fa-bars')
    ->url(fn (Category $record) => ProductResource::url(parameters: ['filters' => ['category' => $record->id]])),

// and in ProductResource, the filter it sets
SelectFilter::make('category')->relationship('category', 'name'),
```

## Extra HTML, JavaScript and CSS

```php
// Before: in cbInit()
$this->pre_index_html = '<div class="alert alert-info">Prices include tax.</div>';
$this->load_js[] = asset('js/products.js');
$this->style_css = '.price { font-weight: bold; }';

// After: in a service provider's boot()
use CrudBooster\Facades\Panel;

Panel::renderHook('index.before', fn ($resource) => $resource === ProductResource::class
    ? '<div class="alert alert-info">Prices include tax.</div>' : '');
Panel::renderHook('body.end', fn () => '<script src="'.asset('js/products.js').'"></script>');
Panel::renderHook('head', fn () => '<style>.price { font-weight: bold; }</style>');
```

## Statistics and custom screens

A statistic or a custom view that is not a list of records becomes a [page](./panel-pages.md):

```php
// Before: $this->index_statistic in cbInit(), or a getReport() method with its own view
$this->index_statistic[] = ['label' => 'Total Products', 'count' => DB::table('products')->count(),
    'icon' => 'fa fa-cube', 'color' => 'success'];
```

```bash
php artisan crudbooster:make-page ProductReport
```

```php
// After: app/Admin/Pages/ProductReport.php
namespace App\Admin\Pages;

use App\Models\Product;
use CrudBooster\Pages\Page;

class ProductReport extends Page
{
    protected static ?string $navigationIcon = 'fa fa-bar-chart';

    protected static string $view = 'admin.pages.product-report';

    public function data(): array
    {
        return ['total' => Product::count()];
    }
}
```

```blade
{{-- resources/views/admin/pages/product-report.blade.php --}}
<x-cb::grid>
    <x-cb::stat label="Total Products" :value="$total" icon="fa fa-cube" color="success" />
</x-cb::grid>
```

## Helpers

| Before | After |
| --- | --- |
| `CRUDBooster::myId()` | `app(AuthenticationServiceInterface::class)->currentUserId()` |
| `CRUDBooster::isSuperadmin()` | `app(AuthenticationServiceInterface::class)->isSuperadmin()` |
| `CRUDBooster::me()->name` | `CmsUser::find(app(AuthenticationServiceInterface::class)->currentUserId())->name` |
| `CRUDBooster::getSetting('appname')` | `app(SettingsRepositoryInterface::class)->get('appname')` |
| `CRUDBooster::isUpdate()` | `$this->can('update', $record)` inside a resource |
| `CRUDBooster::insertLog('Exported products')` | `app(AuditLoggerInterface::class)->log('Exported products')` |
| `CRUDBooster::sendEmail(['to' => $email, 'template' => 'welcome', 'data' => $data])` | `app(TemplateMailerInterface::class)->send(['to' => $email, 'template' => 'welcome', 'data' => $data])` |
| `CRUDBooster::uploadFile('photo')` | `app(FileUploaderInterface::class)->upload('photo')`, or a `FileUpload` field |
| `CRUDBooster::redirect($url, 'Saved', 'success')` | `return redirect($url)->with(['message' => 'Saved', 'message_type' => 'success']);` |
| `CRUDBooster::mainpath()` | `ProductResource::url()` |
| `CRUDBooster::adminPath('products')` | `url(config('crudbooster.ADMIN_PATH').'/products')` |
| `CRUDBooster::first('products', $id)` | `Product::find($id)` |
| `cbLang('home')` | `__('crudbooster::crudbooster.home')` |
| `get_setting('appname')` | `app(SettingsRepositoryInterface::class)->get('appname')` |
| `g('search')` | `request('search')` |
| `str_slug($name)`, `str_limit($text, 50)` | `Str::slug($name)`, `Str::limit($text, 50)` |

The interfaces are in `crocodicstudio\crudbooster\contracts`: `Security\AuthenticationServiceInterface`,
`Settings\SettingsRepositoryInterface`, `Audit\AuditLoggerInterface`, `Mail\TemplateMailerInterface` and
`Files\FileUploaderInterface`. Inside a resource or page, ask for them in the constructor instead of
calling `app()`.

## API endpoints

```php
// Before: a generated API controller
class ApiProductsController extends \crocodicstudio\crudbooster\controllers\ApiController
{
    public function __construct()
    {
        $this->table = 'products';
        $this->permalink = 'products';
        $this->method_type = 'get';
    }
}

// After: routes/api.php
Route::middleware(\crocodicstudio\crudbooster\middlewares\CBAuthAPI::class)
    ->get('/products', fn () => \App\Http\Resources\ProductResource::collection(Product::paginate()));
```

`App\Http\Resources\ProductResource` is a Laravel [API resource](https://laravel.com/docs/eloquent-resources)
(`php artisan make:resource ProductResource`), not the admin resource. `CBAuthAPI` accepts the tokens your
apps already have; you can also move to Laravel Sanctum.

## Imports for the examples

| Class | Namespace |
| --- | --- |
| `Resource` | `CrudBooster\Resources` |
| `Table`, `Form` | `CrudBooster\Tables`, `CrudBooster\Forms` |
| `TextColumn`, `BadgeColumn`, `BooleanColumn`, `ImageColumn` | `CrudBooster\Tables\Columns` |
| `SelectFilter`, `TernaryFilter`, `Filter` | `CrudBooster\Tables\Filters` |
| `TextInput`, `Textarea`, `Select`, `Radio`, `CheckboxList`, `Toggle`, `DatePicker`, `FileUpload`, `Hidden`, `ViewField` | `CrudBooster\Forms\Fields` |
| `Section` | `CrudBooster\Forms` |
| `Action`, `BulkAction` | `CrudBooster\Actions` |
| `Page` | `CrudBooster\Pages` |
| `Builder` | `Illuminate\Database\Eloquent` |
| `Model` | `Illuminate\Database\Eloquent` |
| `Collection` | `Illuminate\Support` |
