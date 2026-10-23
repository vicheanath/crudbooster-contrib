# CRUDBooster SOLID Architecture Redesign Specification

## 1. Executive Summary & Architectural Goals

CRUDBooster is a widely used rapid backend and CRUD generator for Laravel. In version 7.x, the framework has been successfully upgraded to support PHP 8.2–8.5 and Laravel 12/13 while preserving the classic 5.x architecture.

However, the classic codebase suffers from classical architectural debt:
- **God Class Anti-Pattern**: `CBController.php` (1,790 lines) and `CRUDBooster.php` (1,600+ lines) encapsulate dozens of unrelated responsibilities (session auth, RBAC permissions, database schema introspection, query generation, complex multi-table joins, form parsing, file uploads, image transformations, audit logging, export/import handling, and view rendering).
- **Hardcoded Static Coupling**: Methods rely directly on static facades (`DB::`, `Session::`, `Request::`, `CRUDBooster::`), preventing unit testing and dependency injection.
- **Modification over Extension (OCP violation)**: Adding a new field type, export format, or custom validation logic currently requires editing large switch/if-else ladders within `CBController`.

### The Core Objective
Redesign the framework according to **SOLID** principles and modern Laravel design patterns while **preserving 100% backward compatibility** for all existing applications, controllers, and modules.

> [!IMPORTANT]
> **Zero Breaking Changes Guarantee**:
> 1. Existing module controllers extending `CBController` and defining `cbInit()`, `$this->table`, `$this->col`, `$this->form`, and `hook_*()` will continue to work without changing a single line of code.
> 2. Existing static calls to `CRUDBooster::*` and `CB::*` will remain intact, operating as backward-compatible facade proxies to underlying container services.
> 3. Existing blade views, routes, database tables, and configuration files will continue to function unchanged.

---

## 2. Current Architecture vs. New SOLID Architecture

### Current Monolithic Architecture

```mermaid
flowchart TD
    UserReq["HTTP Request"] --> Route["CBRouter / routes.php"]
    Route --> LegacyCtrl["Legacy Controller (e.g. AdminShopProductsController)"]
    LegacyCtrl --> CBController["CBController (God Class - 1,790 lines)\n- Query Builder & Join Logic\n- Form Processing & Sanitization\n- File & Image Uploads\n- Export/Import Orchestration\n- View Presentation"]
    CBController <--> CBStatic["CRUDBooster Static Helper (1,600+ lines)\n- Auth & Permissions\n- Schema Inspection\n- Direct DB calls\n- Audit Logs & FCM/Email"]
    CBController --> DB[("Database Direct Queries")]
```

### Proposed SOLID Layered Architecture

```mermaid
flowchart TD
    subgraph Presentation ["1. Presentation Layer"]
        HttpReq["HTTP Request"]
        LegacyCtrl["Existing Module Controllers\n(AdminProductsController)"]
        CBController["CBController (Adapter / Orchestrator)"]
        NewResource["Modern CrudController / Fluent API (Optional)"]
    end

    subgraph FacadeLayer ["Backward-Compatibility Bridge"]
        CRUDBoosterFacade["CRUDBooster & CB Static Facades\n(Proxy to IoC Container)"]
    end

    subgraph Operations ["2. Operations / Actions Layer (SRP)"]
        IndexAction["IndexAction"]
        SaveAction["SaveAction (Create / Update)"]
        DeleteAction["DeleteAction"]
        ExportAction["ExportAction"]
        ImportAction["ImportAction"]
    end

    subgraph DomainServices ["3. Domain Services & Contracts (DIP / ISP)"]
        GridBuilder["GridQueryBuilderInterface"]
        FormProcessor["FormProcessorInterface"]
        SchemaInspector["SchemaInspectorInterface"]
        PermissionMgr["PermissionCheckerInterface"]
        AuditLogger["AuditLoggerInterface"]
    end

    subgraph Extensibility ["4. Extensibility Plugins (OCP)"]
        FieldRegistry["FieldHandlerRegistry"]
        UploadField["UploadFieldHandler"]
        PasswordField["PasswordFieldHandler"]
        RelationField["RelationshipFieldHandler"]
        CustomField["CustomFieldHandler (App-defined)"]
        ExportDrivers["ExporterInterface (Excel, CSV, PDF)"]
    end

    subgraph Persistence ["5. Persistence & Infrastructure Layer"]
        Models["Eloquent Models\n(CmsUser, CmsModul, CmsPrivilege...)"]
        DBLayer["Laravel DB & Schema Connection"]
    end

    HttpReq --> LegacyCtrl
    LegacyCtrl --> CBController
    HttpReq --> NewResource
    CBController --> Operations
    NewResource --> Operations
    CRUDBoosterFacade -. delegates .-> DomainServices

    IndexAction --> GridBuilder
    IndexAction --> PermissionMgr
    SaveAction --> FormProcessor
    SaveAction --> AuditLogger
    ExportAction --> ExportDrivers
    ExportAction --> GridBuilder

    FormProcessor --> FieldRegistry
    FieldRegistry --> UploadField
    FieldRegistry --> PasswordField
    FieldRegistry --> RelationField
    FieldRegistry --> CustomField

    GridBuilder --> SchemaInspector
    GridBuilder --> DBLayer
    FormProcessor --> DBLayer
    PermissionMgr --> Models
```

---

## 3. SOLID Principles Application Matrix

| Principle | Classic CRUDBooster Problem | Redesigned SOLID Solution |
| :--- | :--- | :--- |
| **S - Single Responsibility** | `CBController` and `CRUDBooster` do auth, schema, query building, validation, file upload, export, import, logging. | Decompose into focused services: `SchemaInspector`, `GridQueryBuilder`, `FormProcessor`, `PermissionManager`, `AuditLogger`, `Action` classes. |
| **O - Open / Closed** | Adding new field types or export formats requires editing core controller switch/if statements. | Registry & Strategy patterns: `FieldHandlerRegistry` with `FieldHandlerInterface`, `ExporterRegistry` with `ExporterInterface`. New fields can be added from plugins without touching core files. |
| **L - Liskov Substitution** | Concrete static methods and classes cannot be swapped, subclassed, or mocked in tests. | Controllers and services depend on interfaces (`PermissionCheckerInterface`, `AuditLoggerInterface`, etc.). Custom implementations can replace defaults in the container. |
| **I - Interface Segregation** | Large god-interfaces or monolithic classes force callers to know about everything. | Micro-interfaces: `AuditLoggerInterface`, `PermissionCheckerInterface`, `GridQueryBuilderInterface`, `SchemaInspectorInterface`. Consumers depend only on the methods they use. |
| **D - Dependency Inversion** | High-level modules directly depend on low-level global singletons and static helpers (`Session`, `DB`, `Request`). | High-level operations depend on domain interfaces injected through Laravel's service container. Legacy facades act as lightweight adapters. |

---

## 4. Domain Decomposition & Package Structure

The package keeps its lowercase top-level directories (`controllers/`, `helpers/`, `export/`, ...), so the new
domains follow the same convention. A capitalized `Export/` next to the existing `export/` would collide on
case-insensitive file systems such as the macOS default.

```text
src/
├── contracts/                          <-- [ISP / DIP] Interfaces
│   ├── Actions/CrudActionInterface.php
│   ├── Audit/AuditLoggerInterface.php
│   ├── Export/ExporterInterface.php
│   ├── Files/FileUploaderInterface.php
│   ├── Forms/FieldHandlerInterface.php, FormProcessorInterface.php, FormValidatorInterface.php
│   ├── Generator/ModuleGeneratorInterface.php, ApiGeneratorInterface.php
│   ├── Grid/GridQueryBuilderInterface.php, GridFilterParserInterface.php
│   ├── Mail/TemplateMailerInterface.php
│   ├── Navigation/MenuRepositoryInterface.php
│   ├── Notifications/NotificationSenderInterface.php
│   ├── Schema/SchemaInspectorInterface.php
│   ├── Security/AuthenticationServiceInterface.php, PermissionCheckerInterface.php
│   └── Settings/SettingsRepositoryInterface.php
│
├── security/                           <-- Auth & RBAC
│   ├── AdminAuthenticationService.php  (login, logout, lock screen, password reset)
│   ├── AdminAuthenticationContext.php
│   ├── SessionAuthenticationService.php (implements AuthenticationServiceInterface)
│   └── PermissionManager.php           (implements PermissionCheckerInterface)
├── audit/AuditLogger.php               (implements AuditLoggerInterface)
├── schema/SchemaInspector.php          (columns, primary keys, types, nullability, cached)
│
├── grid/                               <-- Index page
│   ├── GridQueryBuilder.php            (joins, subqueries, search, where, filters, ordering)
│   ├── GridFilterParser.php            (the filter_column URL format)
│   ├── GridQuery.php                   (query + resolved columns DTO)
│   └── GridRowRenderer.php             (cell HTML, row buttons, hook_row_index)
│
├── forms/                              <-- Input handling
│   ├── FormContext.php                 (table, primary key, create/edit, record id)
│   ├── FormProcessor.php               (input_assignment and the after-save step)
│   ├── FormValidator.php               (validation)
│   ├── FieldHandlerRegistry.php        (OCP: type => handler)
│   └── handlers/                       BaseFieldHandler, Text, Money, Select, Select2, Checkbox,
│                                       Multitext, GoogleMaps, Upload, FileManager, Child (+ SyncsPivotTable)
├── files/FileUploader.php              (uploads, image resizing, thumbnails)
│
├── actions/                            <-- One class per endpoint
│   ├── CrudAction.php                  (shared permission check / logging)
│   ├── IndexAction.php, FormAction.php (add/edit forms), DetailAction.php, SaveAction.php,
│   ├── DeleteAction.php (+ delete image), BulkAction.php, ExportAction.php, ImportAction.php
│   └── LookupAction.php                (find-data, data-table, modal-data)
│
├── export/                             <-- Strategy-based exporters (OCP)
│   ├── ExporterRegistry.php, PdfExporter.php, ExcelExporter.php, CsvExporter.php
│   └── DefaultExportXls.php, DefaultImportRows.php (unchanged)
│
├── settings/SettingsRepository.php     (cms_settings, cached under the 5.x "setting_{name}" keys)
├── navigation/MenuRepository.php       (sidebar and dashboard menus for the current privilege)
├── mail/TemplateMailer.php             (email templates, the queue, the SMTP mailer)
├── notifications/NotificationSender.php (cms_notifications)
├── generator/                          <-- Module and API code generators
│   ├── ModuleGenerator.php, ApiGenerator.php
│   └── stubs/module_controller.stub, api_controller.stub
│
├── compatibility/CrudConfig.php        <-- Immutable module configuration (props <-> fluent API)
├── modern/                             <-- Phase 6 fluent API
│   ├── CrudController.php, CrudDefinition.php, Column.php, Field.php
│
├── controllers/CBController.php        <-- Same public API, delegates to actions/
├── helpers/CRUDBooster.php, CB.php     <-- Same static API, delegates to contracts
└── CRUDBoosterServiceProvider.php      <-- Container bindings
```

---

## 5. Backward Compatibility Strategy

How do we guarantee that existing modules like `AdminShopProductsController` continue working seamlessly without any edits?

### 1. The Controller Adapter Pattern
In existing code:
```php
class AdminShopProductsController extends CBController
{
    public function cbInit()
    {
        $this->table = 'shop_products';
        $this->primary_key = 'id';
        $this->col = [...];
        $this->form = [...];
    }
}
```

In the redesigned `CBController`:
- All public properties (`$this->table`, `$this->col`, `$this->form`, `$this->button_*`, hooks) remain exactly as they are.
- In `cbLoader()`, `CBController` packages its configuration properties into an immutable `CrudConfigDTO`.
- Action methods (such as `getIndex()`, `postAddSave()`, `getDelete()`) simply resolve their corresponding Action class from the container and execute it, passing `$this` or the config DTO:

```php
// Inside redesigned CBController.php:
public function getIndex()
{
    $this->cbLoader();
    $action = app(IndexAction::class);
    return $action->execute($this);
}

public function postAddSave()
{
    $this->cbLoader();
    $action = app(SaveAction::class);
    return $action->execute($this, mode: 'create');
}
```
Existing hooks (`hook_before_add`, `hook_query_index`, etc.) are invoked by the Action classes via the controller reference, preserving the exact execution lifecycle.

### 2. The Facade Proxy Pattern for `CRUDBooster`
Currently, user code and views call static methods:
`CRUDBooster::myId()`
`CRUDBooster::isCreate()`
`CRUDBooster::insertLog($text)`
`CRUDBooster::getSetting($name)`

In the redesigned `CRUDBooster.php`:
The static methods remain, but their internal implementations become one-line delegations to the container interface:

```php
// Inside helpers/CRUDBooster.php:
public static function myId(): ?int
{
    return app(AuthenticationServiceInterface::class)->currentUserId();
}

public static function isCreate(): bool
{
    return app(PermissionCheckerInterface::class)->canCreate();
}

public static function insertLog(string $description, string $details = ''): void
{
    app(AuditLoggerInterface::class)->log($description, $details);
}

public static function getTableColumns(string $table): array
{
    return app(SchemaInspectorInterface::class)->getColumnNames($table);
}
```

This transforms `CRUDBooster` from a God Helper into a convenient Laravel Facade, while business logic resides in testable, injectable services.

---

## 6. Detailed Design of Key SOLID Components

### A. The Extensible Field Handler System (OCP)
Currently, `CBController::input_assignment()` has a 200-line `switch($ro['type'])` with hardcoded cases for `upload`, `password`, `checkbox`, `select2`, etc.

**New SOLID Design**:
1. `FieldHandlerInterface`:
```php
namespace crocodicstudio\crudbooster\contracts\Forms;

interface FieldHandlerInterface
{
    public function supports(string $type): bool;

    public function beforeSave(array $field, mixed $value, array &$payload, FormContext $context): void;

    public function afterSave(array $field, mixed $value, FormContext $context): void;
}
```
`FormContext` carries the module's table, primary key, the mode (create/edit) and the record id. The record id
alone is not enough, because pivot handlers also need the owning table to find their foreign keys.

2. Dedicated Handlers (SRP):
- `UploadFieldHandler`: Handles file validation, disk storage, image resizing, and path generation.
- `PasswordFieldHandler`: Handles bcrypt/argon hashing or skipping empty passwords on edit.
- `RelationshipFieldHandler`: Handles many-to-many pivot insertions and child table sync.

3. `FieldHandlerRegistry` (OCP):
Developers or plugins can register custom field types dynamically:
```php
$registry->register('signature', new SignatureFieldHandler());
$registry->register('upload', S3UploadFieldHandler::class); // replace a built-in type
```
Core controllers no longer need to be modified when new UI field types are introduced.

### B. Clean Grid Query Builder (SRP & DIP)
Currently, `CBController::getIndex()` contains over 400 lines of raw SQL string concatenation, alias generation, and nested join logic.

**New SOLID Design**:
- `GridQueryBuilder` is responsible purely for taking the table, column specifications, filters, and soft delete settings and returning a prepared Eloquent/Database query builder.
- Can be tested in isolation with SQLite in-memory without invoking any HTTP controllers.

```php
namespace crocodicstudio\crudbooster\Contracts\Grid;

interface GridQueryBuilderInterface
{
    // Returns the prepared query plus the columns resolved for the views (field, field_with, type_data...).
    public function buildQuery(string $table, string $primaryKey, array $columns, array $options = []): GridQuery;

    public function applyFilters(Builder $query, array $filterParams): bool;
}
```

### C. Permissions & Security (ISP)
Split the auth and permission duties:
- `AuthenticationServiceInterface`: Who is the user? (login, logout, lockscreen, user context).
- `PermissionCheckerInterface`: What can the user do in the current module? (`canRead()`, `canCreate()`, `canUpdate()`, `canDelete()`, `canView()`).

Controllers and blade directives only ask `PermissionCheckerInterface`, making it simple to plug in Laravel's native Gates/Policies or Spatie Permission in the future if desired.

---

## 7. Migration & Implementation Roadmap

```mermaid
flowchart LR
    P1["Phase 1\nContracts & IoC"] --> P2["Phase 2\nSecurity & Audit"]
    P2 --> P3["Phase 3\nSchema & Query Builder"]
    P3 --> P4["Phase 4\nForm & Field Handlers"]
    P4 --> P5["Phase 5\nAction Extraction"]
    P5 --> P6["Phase 6\nModern Resource API"]
```

Before Phase 3, `tests/Feature/CrudBehaviourTest.php` pinned down what a classic module does today. It covers
every field type, joins, subquery columns, search, filters, soft deletes, hooks and their order, bulk actions,
export and lookups. Those tests passed against the pre-refactor code, except where a bug was fixed on purpose
(see section 9), and they pass unchanged after it.

### Phase 1: Contracts & Service Container Setup — **Implemented.**
### Phase 2: Security, Permissions & Audit Logging — **Implemented.**
`CRUDBooster::myId()`, `isSuperadmin()`, `isView/Create/Read/Update/Delete()` and `insertLog()` delegate to
the security and audit contracts.

### Phase 3: Schema Inspector & Grid Query Builder — **Implemented.**
- `SchemaInspector` also owns column types and nullability (same cache keys as 5.x).
- `GridQueryBuilder` builds the whole index query and `GridRowRenderer` the cell HTML.

### Phase 4: Form Processor & Field Handler Registry — **Implemented.**
- `validation()` and `input_assignment()` stay public on `CBController`, because modules override and call them.
  They delegate to `FormValidator` and `FormProcessor`.
- The after-save loop (pivot tables, child rows) became each handler's `afterSave()`.
- Uploads moved to `FileUploader`. `CRUDBooster::uploadFile()` and `uploadBase64()` delegate to it.
- Password hashing depends on the field name (`PASSWORD_FIELDS_CANDIDATE`), not on the `password` type, so it
  lives in `BaseFieldHandler` for every type instead of in a separate `PasswordFieldHandler`.

### Phase 5: Action Orchestrator Refactoring — **Implemented.**
- `CBController` shrank from 1,790 to about 440 lines, and most of the rest are its public properties and
  hook stubs. Each endpoint calls `cbLoader()` and then `app(SomeAction::class)->execute($this, ...)`.
- Actions read the controller's live properties and call its hooks and overridable methods (`getIndex()`,
  `validation()`, `input_assignment()`, `actionButtonSelected()`). This keeps the 5.x behaviour of hooks that
  change `$this->arr` or `$this->return_url`, and of modules that override one step.

### Phase 6 (Bonus): Optional Modern CrudController API — **Implemented.**
`modern\CrudController` + `CrudDefinition`, `Column` and `Field` builders. The definition becomes a
`compatibility\CrudConfig`, which is applied to the classic properties, so everything downstream is shared.
See [docs/en/how-to-extend.md](en/how-to-extend.md).

### Phase 7: Remaining helper domains — **Implemented.**
The helper still held whole domains after Phase 5. Each now has a contract, and the static method delegates to it:

| Static helper | Contract | Implementation |
| :--- | :--- | :--- |
| `getSetting()` | `SettingsRepositoryInterface` | `settings\SettingsRepository` |
| `sidebarMenu()`, `sidebarDashboard()` | `MenuRepositoryInterface` | `navigation\MenuRepository` |
| `sendEmail()`, `sendEmailQueue()`, `mailer()` | `TemplateMailerInterface` | `mail\TemplateMailer` |
| `sendNotification()` | `NotificationSenderInterface` | `notifications\NotificationSender` |
| `generateController()`, `getNameTable()` | `ModuleGeneratorInterface` | `generator\ModuleGenerator` |
| `generateAPI()` | `ApiGeneratorInterface` | `generator\ApiGenerator` |

- `tests/Feature/CodeGeneratorTest.php` pins the exact generated source against `tests/fixtures/generated/`,
  because users keep and edit those files. The fixtures were recorded from the pre-refactor generator, and the
  stubs reproduce them byte for byte. Set `CB_UPDATE_GOLDEN=1` to re-record them after an intended change.
- `AuditLogger`, `AdminAuthenticationService` and the `mailqueues` command take these contracts by injection
  instead of calling the static helper.
- `CRUDBooster.php` went from 1,735 to about 900 lines. What is left is mostly request context (`mainpath()`,
  `getCurrentModule()`, `redirect()`), the small query shortcuts (`first()`, `get()`, `insert()`) and
  `routeController()`.

---

## 8. Summary of Benefits

1. **Maintainability**: `CBController.php` shrinks from 1,790 lines to ~440, most of it the documented public properties and hook stubs.
2. **Testability**: Every component (Query Builder, Form Processor, Field Handlers, Permissions) can be unit-tested independently with mocks.
3. **Extensibility**: Adding custom fields or export drivers requires writing a single class and registering it, without modifying vendor code.
4. **Reliability**: Legacy projects upgrade with zero breaking changes; classic controllers and static helper methods continue to behave identically.

---

## 9. Implementation Notes

### Deviations from the original plan
- **Directory case**: lowercase domain directories (see section 4).
- **No `CBControllerBridge`**: each endpoint delegates in one line, so a trait or delegator would only add
  indirection.
- **`CrudConfig` is a snapshot, not the actions' input**: hooks may change controller properties mid-request,
  so actions read the controller. `CrudConfig` links the classic properties and the fluent API, and
  `CBController::crudConfig()` exposes it read-only.
- **`ImportAction` and `LookupAction`** expose one method per step or endpoint instead of `execute()`, because
  each covers several routes.
- **`ExporterInterface::label()`**: the export dialog lists the registered formats, so custom exporters show up
  in the UI.
- **`AuditLogger` applies the `api_debug_mode` check** that `CRUDBooster::insertLog()` used to do, so every
  caller follows the same policy.

### Bugs fixed along the way
Found by the characterization tests or while moving code:
- A `child` field submitted without rows inserted an empty child row, which failed on NOT NULL columns.
- The `googlemaps` form component crashed with "Array to string conversion" (`$row->$form['latitude']` parses
  differently since PHP 7).
- The `str_limit` column option called `str_limit()`, which Laravel 6 removed.
- Audit log entries lost `created_at` in the Phase 2 extraction.
- `hide_form` was passed to `unserialize()` with objects allowed (PHP object injection). It now allows plain
  values only.
- The select2 `find-data` lookup interpolated the search term into SQL when a `format` was set. It is now a
  bound parameter.
- A bare `unique` rule used a table literally named "unique". It now means the module's table.
- A string `$orderby` with a qualified column (`"table.column,desc"`) produced `table.table.column`.
- `getIndex()` no longer turns a string `$orderby` into an array, which broke a second call on the same instance.
- The `where[column]=value` index parameter used the table of the last listed column instead of the
  module's table.
- XLS/CSV export failed on any cell containing `&` or `<` (found in the playground: the empty-image placeholder
  URL has a bare `&`). The export view escapes cell values for these formats.
- Import no longer calls `exit()` when creating a related row fails; the error surfaces as an exception.
- The forgot-password email showed a literal `[password]`. The user model was passed as template data, and
  iterating a model yields its public properties, not its attributes. Models now contribute their attributes.
- Generating a module for a table with an `is_*` column crashed with "Array to string conversion" (before PHP 8
  it wrote `"dataenum"=>"Array"`). The options are now written as `"1|Paid;0|Un-Paid"`.
- A third module on the same table overwrote `Admin{Name}1Controller`, and a module generated without a name
  became `Admin1Controller`. The generator now picks the first free number after the table or module name.
- Queued email attachments were passed to `unserialize()` with objects allowed. Only plain values are revived now.
- Sending an email with a template slug that does not exist failed with "Attempt to assign property on null". It
  now throws an `InvalidArgumentException` that names the slug.
- The sidebar menu queries interpolated the session's privilege id into SQL. It is now a bound parameter.
