# Changelog

## Unreleased

### SOLID architecture (no changes needed in existing modules)
Implements [docs/CRUDBOOSTER_SOLID_ARCHITECTURE.md](docs/CRUDBOOSTER_SOLID_ARCHITECTURE.md). `CBController` and the
`CRUDBooster` helper keep their public API but delegate to services bound in the container:
- Contracts in `src/contracts` for authentication, permissions, audit log, schema, grid query, filters, forms,
  validation, uploads, exporters and actions. Bind your own implementation to replace any of them.
- `CBController` endpoints run action classes (`src/actions`). `CBController.php` went from 1,790 to about 440 lines.
- Form field types are handlers in a `FieldHandlerRegistry`: register new types or replace built-in ones.
- Export formats come from an `ExporterRegistry`. Registered formats appear in the export dialog.
- Optional fluent module API: `modern\CrudController` with `CrudDefinition`, `Column` and `Field`.
- New guide: How To Extend CRUDBooster (`docs/en/how-to-extend.md`).
- About 30 new tests, including characterization tests that pin down classic module behaviour.
- Settings, sidebar menus, template email, notifications and the module/API code generators are services too
  (`SettingsRepositoryInterface`, `MenuRepositoryInterface`, `TemplateMailerInterface`,
  `NotificationSenderInterface`, `ModuleGeneratorInterface`, `ApiGeneratorInterface`). The generators render from
  stubs, and a golden-file test pins the generated source byte for byte.

- CRUDBooster's own tables are read and written through the Eloquent models in `src/models`, with named scopes
  for shared queries. New services: `PrivilegeRolesInterface` (role matrix and session roles) and
  `ModuleRegistrarInterface` (a module's menus). Module data stays on the query builder. See Phase 8 in
  `docs/CRUDBOOSTER_SOLID_ARCHITECTURE.md`.
- Tests are split into `Unit` and `Feature` suites (`composer test:unit`), and CI reports coverage
  (`composer test:coverage` locally).

### Changed
- Header and sidebar typography: one font stack, a light header shadow, uppercase section labels, tighter menu items
  and ellipsis for long names. Colours still come from each privilege's skin. Run `php artisan crudbooster:update`
  to republish the CSS.
- The navbar, user menu and sidebar panel show the admin's initials instead of the profile photo, in AdminLTE
  2.3.8's avatar slots and sizes.

### Fixed
- A `child` form field submitted without rows inserted an empty child row (failed on NOT NULL columns).
- The `googlemaps` form component crashed with "Array to string conversion".
- The `str_limit` grid column option crashed (`str_limit()` was removed in Laravel 6; the helper is back).
- Security: the `hide_form` input is unserialized with objects disallowed, and the select2 lookup's search term is
  bound instead of interpolated into SQL.
- XLS and CSV export failed when a cell contained `&` or `<`, including the "no image" placeholder of every empty
  image column. Values are now exported as escaped text.
- A bare `unique` validation rule now checks the module's table.
- A string `$orderby` with a qualified column (`"table.column,desc"`) and the `where[...]` index parameter built
  wrong column names.
- The forgot-password email showed a literal `[password]` instead of the new password.
- Generating a module for a table with an `is_*` column crashed with "Array to string conversion".
- A third module on the same table overwrote `Admin{Name}1Controller`; a module generated without a name became
  `Admin1Controller`.
- Sending email with an unknown template slug now throws a clear `InvalidArgumentException`.
- On MySQL, table columns were read in no guaranteed order, so the Module Generator could shuffle grid columns
  and form fields.
- Security: API key, API generator, statistic builder and setting-file endpoints that a standard admin could call
  now require a superadmin, like the rest of those tools.
- A module created with the Module Manager's add form had no permissions and an invisible menu group.
- Deleting a module without a controller deleted every menu; deleting a module left its empty menu group.
- The notification bell listed read broadcast notifications, and any user's notification could be marked read.
- Saving the default export paper size did not refresh the cached setting.
- Security: queued email attachments are unserialized with objects disallowed, and the sidebar menu queries bind
  the privilege id instead of interpolating it into SQL.

- Documentation site built with VitePress and deployed to GitHub Pages: https://vicheanath.github.io/crudbooster-contrib/

## v7.0.2

- Fields submitted empty skip validation again, as in 5.x. 7.0.0 validated required-but-empty fields, which broke the common pattern of a hidden `required` field (e.g. `site_id`) filled by `hook_before_add/edit`, so those forms could not be saved.
- The edit form now requires update permission. Read-only roles could open it before (saving was already refused).

## v7.0.1

- Delete confirmation uses SweetAlert2's `Swal.fire()` on pages that load it. SweetAlert2 also takes over the global `swal`, so the legacy call silently never deleted. Extra options via the new `SWEETALERT2_DELETE_OPTIONS` config key.
- UPGRADE.md: apps that copied the CRUDBooster helper class into `autoload.files` must remove it.
- Contributor setup: committed e-commerce playground wired to the repo, optional Docker MySQL + Mailpit, `composer test:mysql`, CONTRIBUTING.md and issue/PR templates.
- Forgot-password email had no subject, and every `crudbooster:update` added another copy of its template.

## v7.0.0

First release as `vicheanath/crudbooster`: the classic CRUDBooster 5.x line on current PHP and Laravel.
See [UPGRADE.md](UPGRADE.md).

### Platform
- PHP 8.2 – 8.5, Laravel 12 / 13 (Laravel 7/8 and 11 dropped).
- DomPDF wrapper 3.x, Intervention Image 3.x (imagecache removed), Laravel Excel 3.1.70+ / 4.x, doctrine/dbal removed.
- CI on PHP 8.2–8.5 × Laravel 12/13 and MySQL 8.4, with about 85 feature tests covering every core module.

### Fixed for PHP 8 / modern Laravel
- `DB::raw()` inside `DB::select()` (fatal on Laravel 10+): export query, statistic widgets, `dataquery` options,
  column type/nullability lookups.
- Statistic dashboards crashed: SQL widgets passed session arrays to `str_replace()`.
- The sidebar crashed when no dashboard menu existed.
- `count(null)` when saving an API without parameters.
- Undefined key/property notices that PHP 8 turned into exceptions (compatibility handler, can be switched off).
- Dynamic properties in generated controllers (PHP 8.2).

### Restored features
- Data import (it used the removed Laravel Excel 2.x `Excel::load()`).
- `crudbooster:install` (seeder class resolution) and `crudbooster:update` (crashed without laravel-filemanager,
  prompted on `vendor:publish`).
- Email: CRUDBooster SMTP settings are applied again; the `mailqueues` command deletes sent emails from the right table.
- Push notifications via FCM HTTP v1 (the legacy API was shut down).
- Form fields and detail rows: component lookup no longer depends on the package's vendor path.
- Uploads follow the configured `local` disk root (Laravel 11+ `storage/app/private`).

### Other fixes
- API: tokens starting with `B`, `e`, `a` or `r` were randomly rejected (403). `ltrim()` stripped those characters along with the `Bearer ` prefix.
- API token lifetime now matches the advertised 48 hours.
- Redirect helpers no longer `exit` (Octane/test friendly).
- The seeder links the admin user to the real Super Administrator privilege id.
