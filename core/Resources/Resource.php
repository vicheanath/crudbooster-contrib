<?php

namespace CrudBooster\Resources;

use CrudBooster\Auth\Authorizer;
use CrudBooster\Forms\Form;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * An admin section for one Eloquent model: list, create, view, edit and delete pages, a
 * sidebar link and permissions, from one class.
 *
 *     class ProductResource extends Resource
 *     {
 *         protected static string $model = Product::class;
 *         protected static ?string $navigationIcon = 'fa fa-cube';
 *
 *         public function table(Table $table): Table { return $table->columns([...]); }
 *         public function form(Form $form): Form { return $form->schema([...]); }
 *     }
 *
 * Place it in app/Admin/Resources to have it discovered, or register it with
 * Panel::resources(). Resources are resolved from the container, so the constructor may
 * ask for services.
 */
abstract class Resource
{
    /** @var class-string<Model> */
    protected static string $model;

    /** The URL segment after the admin path; defaults to the kebab-cased plural model name. */
    protected static ?string $slug = null;

    protected static ?string $label = null;

    protected static ?string $pluralLabel = null;

    /** The attribute that names a record in titles, breadcrumbs and messages. */
    protected static ?string $recordTitleAttribute = null;

    protected static ?string $navigationIcon = 'fa fa-circle-o';

    protected static ?string $navigationGroup = null;

    protected static int $navigationSort = 0;

    protected static bool $shouldRegisterNavigation = true;

    /** The pages this resource has; leave one out to remove it (and its routes). */
    protected static array $pages = ['index', 'create', 'view', 'edit'];

    abstract public function table(Table $table): Table;

    abstract public function form(Form $form): Form;

    /**
     * The detail page. Defaults to the table's columns.
     */
    public function detail(Detail $detail): Detail
    {
        return $detail->entries($this->table(new Table)->getColumns());
    }

    /**
     * Constrains every page of the resource, e.g. to the current tenant's records.
     */
    public function modifyQuery(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Changes the validated attributes before a record is created.
     */
    public function beforeCreate(array $data): array
    {
        return $data;
    }

    public function afterCreate(Model $record): void
    {
    }

    /**
     * Changes the validated attributes before a record is updated.
     */
    public function beforeUpdate(array $data, Model $record): array
    {
        return $data;
    }

    public function afterUpdate(Model $record): void
    {
    }

    public function beforeDelete(Model $record): void
    {
    }

    public function afterDelete(Model $record): void
    {
    }

    /**
     * Whether the current admin may viewAny, view, create, update or delete. Asks the model's
     * policy if it has one, otherwise CRUDBooster's privileges (see Auth\PrivilegeAuthorizer).
     */
    public function can(string $ability, ?Model $record = null): bool
    {
        $page = ['create' => 'create', 'update' => 'edit', 'view' => 'view'][$ability] ?? null;
        if ($page && ! static::hasPage($page)) {
            return false;
        }

        return app(Authorizer::class)->allows($ability, static::getSlug(), $record ?? static::getModel());
    }

    public function query(): Builder
    {
        return $this->modifyQuery(static::newModel()->newQuery());
    }

    public function resolveRecord(mixed $key): Model
    {
        return $this->query()->whereKey($key)->firstOrFail();
    }

    /** @return class-string<Model> */
    public static function getModel(): string
    {
        return static::$model;
    }

    public static function newModel(): Model
    {
        return new (static::getModel());
    }

    public static function getSlug(): string
    {
        return static::$slug ?? Str::kebab(Str::pluralStudly(class_basename(static::getModel())));
    }

    public static function getLabel(): string
    {
        return static::$label ?? Str::headline(class_basename(static::getModel()));
    }

    public static function getPluralLabel(): string
    {
        return static::$pluralLabel ?? Str::plural(static::getLabel());
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon;
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }

    public static function getNavigationSort(): int
    {
        return static::$navigationSort;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::$shouldRegisterNavigation && static::hasPage('index');
    }

    public static function hasPage(string $page): bool
    {
        return in_array($page, static::$pages, true);
    }

    public static function getPages(): array
    {
        return static::$pages;
    }

    public function getRecordTitle(Model $record): string
    {
        $attribute = static::$recordTitleAttribute;

        return $attribute ? (string) $record->getAttribute($attribute) : static::getLabel().' #'.$record->getKey();
    }

    /**
     * A page URL: url(), url('create'), url('edit', $record), url('index', parameters: ['search' => 'x']).
     */
    public static function url(string $page = 'index', Model|int|string|null $record = null, array $parameters = []): string
    {
        if ($record !== null) {
            $parameters = ['record' => $record instanceof Model ? $record->getKey() : $record] + $parameters;
        }

        return route(static::getRouteName($page), $parameters);
    }

    /**
     * Where to go after saving or deleting: the list, or the dashboard for a resource without one.
     */
    public static function getIndexUrl(): string
    {
        return static::hasPage('index') ? static::url() : url(trim((string) config('crudbooster.ADMIN_PATH'), '/'));
    }

    public static function getRouteName(string $page): string
    {
        return 'cb.resources.'.static::getSlug().'.'.$page;
    }
}
