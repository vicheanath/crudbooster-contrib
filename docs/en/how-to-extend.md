# How To Extend CRUDBooster

Modules written for 5.x work unchanged: a controller extends `CBController`, fills its properties in `cbInit()` and overrides `hook_*()` methods. Under that API the work is split into small services bound in Laravel's container, so you can add or replace a piece without editing vendor code.

| Piece | Contract (`crocodicstudio\crudbooster\contracts\...`) | Default |
|---|---|---|
| Current user | `Security\AuthenticationServiceInterface` | `security\SessionAuthenticationService` |
| Module permissions | `Security\PermissionCheckerInterface` | `security\PermissionManager` |
| Activity log | `Audit\AuditLoggerInterface` | `audit\AuditLogger` |
| Table metadata | `Schema\SchemaInspectorInterface` | `schema\SchemaInspector` |
| Index query | `Grid\GridQueryBuilderInterface` | `grid\GridQueryBuilder` |
| URL filters | `Grid\GridFilterParserInterface` | `grid\GridFilterParser` |
| Form data | `Forms\FormProcessorInterface` | `forms\FormProcessor` |
| Form validation | `Forms\FormValidatorInterface` | `forms\FormValidator` |
| File uploads | `Files\FileUploaderInterface` | `files\FileUploader` |

The static helpers delegate to these services. For example, `CRUDBooster::isCreate()` asks `PermissionCheckerInterface`, and `CRUDBooster::insertLog()` calls `AuditLoggerInterface`.

## Add a form field type

Each field type has a handler. Register yours, usually in `AppServiceProvider::boot()`:

```php
use crocodicstudio\crudbooster\forms\FieldHandlerRegistry;

app(FieldHandlerRegistry::class)->register('slug', \App\Crud\SlugFieldHandler::class);
```

Extend `BaseFieldHandler` to keep the standard behaviour. An empty value is left out when its column is nullable, and fields named like a password are hashed. Then adjust the value:

```php
namespace App\Crud;

use crocodicstudio\crudbooster\forms\handlers\BaseFieldHandler;

class SlugFieldHandler extends BaseFieldHandler
{
    protected function normalize(array $field, mixed $value): mixed
    {
        return str_slug((string) $value);
    }
}
```

Use `['label' => 'Slug', 'name' => 'slug', 'type' => 'slug']` in `$this->form`. The form view needs a component for the type too (see [custom form](./form-custom.md)).

Override `afterSave()` for data that needs the row's id, as the built-in `checkbox`, `select2` and `child` handlers do. You can also replace a built-in type, for example `register('upload', MyS3UploadHandler::class)`.

## Add an export format

```php
use crocodicstudio\crudbooster\export\ExporterRegistry;

app(ExporterRegistry::class)->register('json', \App\Crud\JsonExporter::class);
```

An exporter implements `contracts\Export\ExporterInterface`: `label()` is shown in the export dialog, and `export($data, $options)` returns the download response. `$data['result']` holds the filtered rows and `$data['columns']` the grid columns.

## Replace a service or an action

Bind your own implementation:

```php
$this->app->bind(
    \crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface::class,
    \App\Crud\GatePermissionChecker::class, // e.g. backed by Laravel Gates
);
```

The static `CRUDBooster::` helpers delegate to the same contracts, so a binding also changes them. For example,
`TemplateMailerInterface` handles `CRUDBooster::sendEmail()` and the forgot-password email,
`SettingsRepositoryInterface` handles `CRUDBooster::getSetting()`, and `ModuleGeneratorInterface` handles the
controllers the Module Generator writes. All contracts are in `src/contracts`.

Every `CBController` endpoint runs an action class from `src/actions`, such as `IndexAction`, `SaveAction`, `DeleteAction` and `ExportAction`. These actions call the module's hooks. To change one endpoint for every module, bind a subclass:

```php
$this->app->bind(\crocodicstudio\crudbooster\actions\DeleteAction::class, \App\Crud\ArchiveInsteadOfDelete::class);
```

## Define a module with the fluent API

> **Deprecated since 7.2.** `CrudController` still works in 7.x, but new code should use a [resource](./panel.md). See
> [Deprecated APIs and How To Replace Them](./deprecations.md).

New modules can extend `CrudController` and describe themselves in `setup()` instead of setting properties:

```php
namespace App\Http\Controllers;

use crocodicstudio\crudbooster\modern\Column;
use crocodicstudio\crudbooster\modern\CrudController;
use crocodicstudio\crudbooster\modern\CrudDefinition;
use crocodicstudio\crudbooster\modern\Field;

class AdminProductsController extends CrudController
{
    protected function setup(CrudDefinition $crud): void
    {
        $crud->table('products')
            ->titleField('name')
            ->orderBy('name')
            ->column('Name', 'name')
            ->column(Column::make('Category', 'category_id')->join('categories,name'))
            ->fields(
                Field::text('name')->rules('required|max:255|unique'),
                Field::money('price'),
                Field::select('category_id', 'Category')->datatable('categories,name'),
                Field::checkbox('tags')->datatable('tags,name')->relationshipTable('product_tags'),
            )
            ->withoutButtons('import');
    }

    public function hook_before_add(&$postdata)
    {
        // Hooks work as in any module.
    }
}
```

Any 5.x column or form option can be set as a method. The method name is converted to snake case, so `relationshipTable()` sets `relationship_table`. Use `$crud->set('property', $value)` for any other `CBController` property. Register the module in Module Generator or in `cms_moduls` as usual.
