# Extending The Panel

Every part of the resource layer is a small class you can subclass or replace. Nothing needs to be
registered in a central list.

## Your own field type

Extend `Field` (or an existing field), set its view and override the steps you need:

```php
namespace App\Admin\Fields;

use CrudBooster\Forms\Fields\Field;
use Illuminate\Support\Str;

class SlugInput extends Field
{
    protected string $view = 'admin.fields.slug-input';

    protected function getTypeRules(): array
    {
        return ['alpha_dash'];
    }

    public function dehydrate(mixed $value): mixed
    {
        return Str::slug((string) $value);
    }
}
```

```blade
{{-- resources/views/admin/fields/slug-input.blade.php: receives $field and $state --}}
<div class="col-md-{{ $field->getColumnSpan() }}">
    @include('cb::fields.partials.label')
    <input type="text" name="{{ $field->getName() }}" id="{{ $field->getId() }}" value="{{ $state }}"
           class="form-control @error($field->getName()) is-invalid @enderror">
    @include('cb::fields.partials.help')
</div>
```

Use it like a built-in field: `SlugInput::make('slug')->required()`.

What you can override:

| Method | Purpose |
| --- | --- |
| `getState()` / `getRecordState()` | The value the input shows |
| `getValueFromRequest(Request $request)` | Read the submitted value |
| `getValidationRules()` / `getTypeRules()` | Rules (keyed by input name / added to the field's own) |
| `shouldSave($value)` | `false` skips the attribute, e.g. an empty password |
| `dehydrate($value)` | The value written to the attribute |
| `savesToAttribute()` + `saveRelationships(Model $record, $value)` | Save through a relationship after the record exists |
| `render()` | Full control over the markup |

For a one-off input, `ViewField::make('location')->view('admin.fields.map-picker')` renders your view
without a new class.

## Your own column or filter

```php
use CrudBooster\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;

class ProgressColumn extends Column
{
    protected string $view = 'admin.columns.progress';   // receives $column, $record, $state, $display, $url

    public function formatState(mixed $state, Model $record): mixed
    {
        return min(100, max(0, (int) $state));
    }
}
```

A filter extends `CrudBooster\Tables\Filters\BaseFilter`. It needs a `$view` (the input inside the
filter form, named `filters[{name}]`) and an `applyDefault(Builder $query, mixed $value)` method.

## Render hooks

You can add HTML at fixed places without overriding views:

```php
use CrudBooster\Facades\Panel;

Panel::renderHook('head', fn () => '<link rel="stylesheet" href="'.asset('css/admin.css').'">');
Panel::renderHook('index.before', fn ($resource) => $resource === \App\Admin\Resources\OrderResource::class
    ? view('admin.partials.order-stats') : '');
```

The places are `head`, `body.end`, `index.before`, `index.after`, `form.before`, `form.after`,
`detail.before`, `detail.after`, `page.before` and `page.after`. Closures can ask for `$resource`,
`$record`, `$operation` or `$page`.

## Sidebar links

```php
use CrudBooster\Navigation\NavigationItem;

Panel::navigationItems([
    NavigationItem::make('Pending orders')->url(fn () => OrderResource::url(parameters: ['filters' => ['status' => 'pending']]))
        ->icon('fa fa-clock-o')->group('Shop')->badge(fn () => Order::pending()->count()),
    NavigationItem::make('Help')->url('https://docs.example.com', openInNewTab: true)->icon('fa fa-life-ring'),
])->navigationGroups(['Shop', 'Reports']);   // the order of the groups
```

Panel links appear after the menus from Menu Management and before the superadmin tools.

## Plugins

A plugin packages resources, pages, links and hooks so several apps can share them, for example
through a Composer package:

```php
use CrudBooster\Panel\Panel;
use CrudBooster\Plugins\Plugin;

class BlogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blog';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([PostResource::class, AuthorResource::class])->pages([BlogStats::class]);
    }

    public function boot(Panel $panel): void
    {
        $panel->renderHook('head', fn () => '<link rel="stylesheet" href="/vendor/blog/admin.css">');
    }
}

// In a service provider's boot():
Panel::plugin(new BlogPlugin);
```

## Replacing services

| Contract | Default | Purpose |
| --- | --- | --- |
| `CrudBooster\Auth\Authorizer` | `PrivilegeAuthorizer` (policies, then privileges) | Who may do what |
| `crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface` | `AuditLogger` | The log entries for create, update and delete |

```php
$this->app->bind(\CrudBooster\Auth\Authorizer::class, SpatieAuthorizer::class);
```

## Overriding views

```bash
php artisan vendor:publish --tag=cb_panel_views
```

This copies the panel's views to `resources/views/vendor/cb`, where you can edit them. Keep the
overrides small: a published view does not get fixes from new releases.
