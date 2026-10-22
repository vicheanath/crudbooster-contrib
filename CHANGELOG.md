# Changelog

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
- API token lifetime now matches the advertised 48 hours.
- Required fields submitted empty are validated.
- Redirect helpers no longer `exit` (Octane/test friendly).
- The seeder links the admin user to the real Super Administrator privilege id.
