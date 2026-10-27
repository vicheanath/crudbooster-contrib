# Deprecated APIs and How To Replace Them

From 7.2, the classic module layer is deprecated in favour of [resources and pages](./panel.md)
(the `CrudBooster\` namespace). Everything listed here **still works in every 7.x release**: the
deprecation is a `@deprecated` tag in the source, so your IDE and static analysis (PHPStan,
Psalm) strike the calls through and show the replacement. Nothing is logged and nothing changes at
runtime. The classes and functions may be removed in 8.0, no earlier.

Move at your own pace. Classic modules and resources share the login, layout, sidebar, privileges
and audit log, so you can convert one module at a time.

## What is not deprecated

These are shared by both layers and stay:

- The models (`crocodicstudio\crudbooster\models\*`), migrations and `cms_*` tables.
- The service contracts for authentication, permissions, settings, audit log, mail, notifications,
  schema, menus, uploads and privileges (`contracts/Security`, `Settings`, `Audit`, `Mail`,
  `Notifications`, `Schema`, `Navigation`, `Files`, `Modules`) and their implementations. These
  are the replacements for most `CRUDBooster::*` methods.
- The built-in admin screens (users, privileges, menus, settings, logs, notifications, email
  templates), the `CBBackend` and `CBAuthAPI` middlewares, `CBRouter` and `CBHook`.
- `FCM`, `crudbooster:install`, `crudbooster:update` and `crudbooster:version`.

## Modules: `CBController` → `Resource`

| Deprecated | Replacement |
| --- | --- |
| `CBController` and `cbInit()` | `CrudBooster\Resources\Resource` with `table()` and `form()` |
| `modern\CrudController`, `CrudDefinition`, `Column`, `Field` | The same resource classes |
| `compatibility\CrudConfig` | The resource class itself |
| Module Generator (`ModulsController`, `ModuleGenerator`, `CRUDBooster::generateController()`) | `php artisan crudbooster:make-resource Product --generate` |
| A `cms_moduls` row per module | `php artisan crudbooster:sync-panel` adds resources and pages to **Privileges > Edit** |
| Statistic Builder (`StatisticBuilderController`) | A page: `php artisan crudbooster:make-page` ([Custom Pages](./panel-pages.md)) |

Before:

```php
class AdminProductsController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function cbInit()
    {
        $this->table = 'products';
        $this->title_field = 'name';
        $this->orderby = 'name,asc';
        $this->button_export = false;

        $this->col[] = ['label' => 'Name', 'name' => 'name'];
        $this->col[] = ['label' => 'Category', 'name' => 'category_id', 'join' => 'categories,name'];
        $this->col[] = ['label' => 'Price', 'name' => 'price', 'callback_php' => 'number_format($row->price, 2)'];

        $this->form[] = ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'validation' => 'required|max:255'];
        $this->form[] = ['label' => 'Category', 'name' => 'category_id', 'type' => 'select2', 'datatable' => 'categories,name'];
        $this->form[] = ['label' => 'Price', 'name' => 'price', 'type' => 'money'];
    }

    public function hook_before_add(&$postdata)
    {
        $postdata['created_by'] = CRUDBooster::myId();
    }
}
```

After (`app/Admin/Resources/ProductResource.php`):

```php
use App\Models\Product;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use CrudBooster\Forms\Fields\Select;
use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Columns\TextColumn;
use CrudBooster\Tables\Table;

class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';

    public function __construct(private AuthenticationServiceInterface $auth)
    {
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('price')->numeric(2),
            ])
            ->defaultSort('name');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('category_id')->relationship('category', 'name'),
            TextInput::make('price')->numeric()->prefix('$'),
        ]);
    }

    public function beforeCreate(array $data): array
    {
        return $data + ['created_by' => $this->auth->currentUserId()];
    }
}
```

The resource needs an Eloquent model with the `category()` relationship. Give it a new slug (or
delete the old module first), since two screens cannot share one URL.

### Module properties

| `CBController` property | Resource |
| --- | --- |
| `$table`, `$primary_key` | `$model` (the model's table and key) |
| `$title_field` | `$recordTitleAttribute` |
| `$orderby` | `$table->defaultSort('name', 'asc')` |
| `$limit` | `$table->paginate(25, [25, 50, 100])` |
| `$col` | `$table->columns([...])` |
| `$form` | `$form->schema([...])` |
| `$button_add`, `$button_edit`, `$button_detail` | Leave `create`, `edit` or `view` out of `$pages` |
| `$button_delete`, `$button_table_action` | `$table->withoutDefaultActions()`, or a policy |
| `$button_bulk_action` | `$table->withoutBulkDelete()` |
| `$addaction` | `$table->actions([Action::make(...)])` |
| `$button_selected` + `actionButtonSelected()` | `$table->bulkActions([BulkAction::make(...)])` |
| `$button_filter` | `$table->filters([...])` |
| `$button_export` | A `BulkAction` that returns `Excel::download(...)` or `Pdf::loadView(...)->download()` |
| `$button_import` | A page with `submit()` and a Laravel Excel import |
| `$sub_module`, `$parent_field` | A second resource with a `SelectFilter::relationship()`, linked with `Column::url()` |
| `$table_row_color`, `$index_statistic`, `$alert`, `$pre_index_html`, `$post_index_html` | `Panel::renderHook('index.before', ...)` / `'index.after'` |
| `$load_js`, `$load_css`, `$script_js`, `$style_css` | `Panel::renderHook('head', ...)` / `'body.end'` |
| `$global_privilege` | A model policy, or bind your own `CrudBooster\Auth\Authorizer` |
| `$hide_form` | `Field::onlyOn('create')`, `visible()`, `hidden()` |

### Hooks

| `CBController` hook | Resource |
| --- | --- |
| `hook_query_index(&$query)` | `modifyQuery(Builder $query)` (every page) or `$table->modifyQueryUsing()` (the list only) |
| `hook_row_index($index, &$value)` | `Column::formatStateUsing(fn ($state, $record) => ...)` or `Column::state()` |
| `hook_before_add(&$postdata)` | `beforeCreate(array $data): array` |
| `hook_after_add($id)` | `afterCreate(Model $record)` |
| `hook_before_edit(&$postdata, $id)` | `beforeUpdate(array $data, Model $record): array` |
| `hook_after_edit($id)` | `afterUpdate(Model $record)` |
| `hook_before_delete($id)` | `beforeDelete(Model $record)` |
| `hook_after_delete($id)` | `afterDelete(Model $record)` |

Model observers and events run too, since resources save with Eloquent.

### Columns (`$this->col[]`)

| Column option | Resource column |
| --- | --- |
| `['label' => 'Name', 'name' => 'name']` | `TextColumn::make('name')->label('Name')` |
| `'join' => 'categories,name'` | `TextColumn::make('category.name')` (a relationship, eager loaded) |
| `'callback_php'`, `'callback'` | `formatStateUsing(fn ($state, $record) => ...)` |
| `'image' => true` | `ImageColumn::make('photo')` |
| `'download' => true` | `TextColumn::make('file')->url(fn ($record) => asset($record->file), openInNewTab: true)` |
| `'str_limit' => 50` | `limit(50)` |
| `'nl2br' => true` | `formatStateUsing(fn ($state) => new HtmlString(nl2br(e($state))))` |
| `'visible' => false` | `hidden()` |
| A subquery column | `state(fn ($record) => ...)`, or `withCount()` / `withSum()` in `modifyQuery()` |

### Form types (`$this->form[]`)

| `type` | Resource field |
| --- | --- |
| `text` | `TextInput` |
| `email`, `number`, `password` | `TextInput::make()->email()`, `->numeric()`, `->password()` |
| `money` | `TextInput::make()->numeric()->prefix('$')` |
| `textarea` | `Textarea` |
| `wysiwyg` | `ViewField` with your editor's view |
| `select`, `select2` with `dataenum` | `Select::make()->options([...])` |
| `select`, `select2`, `datamodal` with `datatable` | `Select::make('category_id')->relationship('category', 'name')` |
| `select2` with `relationship_table` | `Select::make('tags')->relationship('tags', 'name')->multiple()` |
| `radio` | `Radio` |
| `checkbox` | `CheckboxList` (with `relationship()` for a pivot table) |
| `date`, `datetime`, `time` | `DatePicker`, `DatePicker::make()->withTime()`, `->timeOnly()` |
| `upload`, `filemanager` | `FileUpload` (`->image()` for images) |
| `hidden` | `Hidden` |
| `header` | `Section::make('Title')->schema([...])` |
| `googlemaps`, `custom` | `ViewField::make()->view('admin.fields.map')`, or your own `Field` subclass |
| `child` | A separate resource for the child model |
| `'validation' => 'required\|min:3'` | `required()`, `rules('min:3')` |
| `'width' => 'col-sm-6'` | `columnSpan(6)` |
| `'help'`, `'placeholder'`, `'readonly'`, `'disabled'` | `helperText()`, `placeholder()`, `disabled()` |

## Extension points of classic modules

These only serve `CBController` modules and are deprecated with them.

| Deprecated | Replacement |
| --- | --- |
| `actions\*` and `CrudActionInterface` | `CrudBooster\Actions\Action` and `BulkAction` |
| `FieldHandlerRegistry`, `FieldHandlerInterface`, `forms\handlers\*` | Extend `CrudBooster\Forms\Fields\Field` ([Extending The Panel](./panel-extending.md)) |
| `FormProcessor`, `FormValidator`, `FormContext` and their contracts | Fields add their own rules and save themselves (`dehydrateStateUsing()`) |
| `GridQueryBuilder`, `GridFilterParser`, `GridQuery`, `GridRowRenderer` and their contracts | `modifyQuery()`, `CrudBooster\Tables\Filters\*`, your own `Column` subclass |
| `ExporterRegistry`, `ExporterInterface`, `ExcelExporter`, `CsvExporter`, `PdfExporter`, `DefaultExportXls`, `DefaultImportRows` | A `BulkAction` that returns a download; your own Laravel Excel export and import classes |

## API Generator

| Deprecated | Replacement |
| --- | --- |
| `ApiController`, `hook_before()`, `hook_after()`, `hook_query()` | A Laravel controller with a form request and a `JsonResource` |
| `ApiCustomController`, `ApiGenerator`, `CRUDBooster::generateAPI()` | Routes in `routes/api.php` |
| `CRUDBooster::authAPI()` | Sanctum, or the `CBAuthAPI` middleware on your routes |

Existing generated APIs keep working, and so do their tokens.

## The `CRUDBooster` helper (and its `CB` alias)

Most methods already delegate to a service. Inject the service (constructor, method or closure
injection), or call `app(Interface::class)`. Contracts live in `crocodicstudio\crudbooster\contracts`.

### Current admin

| Deprecated | Replacement |
| --- | --- |
| `myId()` | `app(Security\AuthenticationServiceInterface::class)->currentUserId()` |
| `isSuperadmin()` | `app(Security\AuthenticationServiceInterface::class)->isSuperadmin()` |
| `me()` | `CmsUser::find($auth->currentUserId())` (a model rather than a plain row) |
| `myName()`, `myPhoto()`, `myPrivilegeId()` | `->name`, `->photo`, `->id_cms_privileges` on that model |
| `myPrivilegeName()` | `->privilege->name` on that model |
| `isLocked()` | `session('admin_lock')` |

### Permissions

| Deprecated | Replacement |
| --- | --- |
| `isView()` | `$resource->can('viewAny')` |
| `isRead()` | `$resource->can('view', $record)` |
| `isCreate()` | `$resource->can('create')` |
| `isUpdate()` | `$resource->can('update', $record)` |
| `isDelete()` | `$resource->can('delete', $record)` |
| `isCRUD()`, `myPrivilege()` | Check each ability with `$resource->can()` |

Outside a resource, ask `app(\CrudBooster\Auth\Authorizer::class)->allows($ability, $slug, $model)`.
A model policy, when there is one, decides first.

### URLs, routes and the current module

| Deprecated | Replacement |
| --- | --- |
| `mainpath()` | `ProductResource::url()` or `route('cb.resources.products.index')` |
| `adminPath($path)` | `url(config('crudbooster.ADMIN_PATH').'/'.$path)`, or `Resource::url()` / `Page::url()` |
| `getModulePath()`, `getCurrentModule()` | `static::getSlug()` and `static::getLabel()` inside a resource or page |
| `getCurrentId()` | The `$record` the hook or action receives |
| `getCurrentMethod()` | `request()->route()->getActionMethod()` |
| `getCurrentMenuId()`, `getCurrentDashboardId()` | `NavigationItem::activeWhen()`; dashboards as pages |
| `sidebarMenu()`, `sidebarDashboard()` | `app(Navigation\MenuRepositoryInterface::class)->sidebar()` / `->dashboard()`; add links with `Panel::navigationItems()` |
| `routeController()` | `Route::get()`, `Route::post()` or `Route::resource()`; resources and pages register their own routes |
| `isExistsController()` | `app(\CrudBooster\Panel\Panel::class)->getResource($slug)` |
| `redirect($to, $msg, $type)` | `return redirect($to)->with(['message' => $msg, 'message_type' => $type]);` |
| `redirectBack($msg, $type)` | `return back()->with(['message' => $msg, 'message_type' => $type]);` |
| `referer()` | `url()->previous()` |
| `getUrlParameters()` | Resource filters, search and sort are query strings: `Resource::url(parameters: [...])` |
| `urlFilterColumn()`, `getValueFilter()`, `getSortingFilter()`, `getTypeFilter()` | `CrudBooster\Tables\Filters\*`, `request('filters.name')`, `request('sort')` |
| `deleteConfirm()` | `Action::make(...)->requiresConfirmation()` |

### Data and schema

| Deprecated | Replacement |
| --- | --- |
| `first($table, $id)`, `get($table, ...)`, `insert($table, $data)` | Eloquent (`Product::find()`, `Product::where()->get()`, `Product::create()`), or `DB::table()` |
| `newId($table)` | Let the database assign ids (auto-increment, or `HasUuids` / `HasUlids`) |
| `pk()`, `findPrimaryKey()` | `(new Product)->getKeyName()`, or `app(Schema\SchemaInspectorInterface::class)->primaryKey($table)` |
| `isColumnExists()`, `isColumnNULL()`, `getFieldType()`, `getTableColumns()` | `SchemaInspectorInterface`: `hasColumn()`, `isNullable()`, `columnType()`, `columns()` |
| `listTables()` | `Schema::getTableListing()` |
| `parseSqlTable()` | `DB::connection($name)->table($table)` |
| `getForeignKey()`, `getTableForeignKey()`, `isForeignKey()` | Relationships on the model (`belongsTo()`, `hasMany()`) |
| `getNameTable()` | `$recordTitleAttribute` |
| `echoSelect2Mult()`, `CB::ES2M()` | A relationship column: `TextColumn::make('tags.name')` |

### Settings, files, mail, notifications, log, cache

| Deprecated | Replacement |
| --- | --- |
| `getSetting($name)` | `app(Settings\SettingsRepositoryInterface::class)->get($name)` |
| `uploadFile()`, `uploadBase64()` | `app(Files\FileUploaderInterface::class)->upload()` / `->uploadBase64()`, or a `FileUpload` field |
| `sendEmail()`, `sendEmailQueue()`, `mailer()` | `app(Mail\TemplateMailerInterface::class)->send()`, `->sendQueued()`, `->mailer()`, or a Laravel `Mailable` |
| `sendNotification($config)` | `app(Notifications\NotificationSenderInterface::class)->send($content, $url, $userIds)` |
| `sendFCM($tokens, $data)` | `FCM::send((array) $tokens, $data)` |
| `insertLog()` | `app(Audit\AuditLoggerInterface::class)->log()`; resources log create, update and delete themselves |
| `valid($rules)` | `$request->validate($rules)`, or `rules()` on resource fields |
| `putCache()`, `getCache()`, `forgetCache()`, `clearCache()`, `flushCache()` | The `Cache` facade |
| `stringBetween()` | `Str::between()` |
| `timeAgo()` | `Carbon::parse($date)->diffForHumans()` |
| `packagePath()` | Internal; no replacement |

## `Image` alias (`CBImage`)

Use Intervention Image v3 directly: `\Intervention\Image\ImageManager::gd()->read($path)`, or the
`intervention/image-laravel` facade. In resources, `ImageColumn` and `FileUpload::image()` cover the
common cases.

## Global functions (`src/helpers/Helper.php`)

| Deprecated | Replacement |
| --- | --- |
| `cbLang($key, $replace)` | `__('crudbooster::crudbooster.'.$key, $replace)` |
| `get_setting($key, $default)` | `app(SettingsRepositoryInterface::class)->get($key) ?: $default` |
| `g($key, $default)` | `request($key, $default)` |
| `db($table)` | `DB::table($table)` |
| `str_random()`, `str_slug()`, `str_limit()`, `ends_with()` | `Str::random()`, `Str::slug()`, `Str::limit()`, `Str::endsWith()` |
| `extract_unit()` | `Str::between()` |
| `now()` | Laravel's `now()` (always defined on Laravel 12+, so this fallback never loads) |
| `assetThumbnail()`, `assetResize()` | `ImageColumn`, or Intervention Image v3 `cover()` |
| `min_var_export()` | `var_export($value, true)` |
| `rrmdir()` | `File::deleteDirectory()` |

## Finding the calls in your app

Static analysis lists every deprecated call. With PHPStan and
[phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules):

```bash
composer require --dev phpstan/phpstan phpstan/phpstan-deprecation-rules phpstan/extension-installer
```

```bash
vendor/bin/phpstan analyse app --level=0
```

Or search for the most common ones:

```bash
grep -rnE "CBController|CRUDBooster::|CB::|cbLang\(|get_setting\(" app resources/views
```
