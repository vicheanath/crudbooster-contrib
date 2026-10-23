# Upgrading from `crocodicstudio/crudbooster` 5.x to `vicheanath/crudbooster` 7.0

`vicheanath/crudbooster` keeps the classic CRUDBooster 5.x architecture (same modules, `cbInit()` controllers,
hooks, views and database tables) and brings it to current PHP and Laravel. The PHP namespace is unchanged
(`crocodicstudio\crudbooster\...`), so generated module controllers and `CBHook` keep working.

## Requirements

| | 5.6 | 7.0 |
|---|---|---|
| PHP | 7.4 | **8.2 – 8.5** (8.3+ for Laravel 13) |
| Laravel | 7 / 8 | **12 / 13** |
| Laravel Excel | 3.1 | 3.1.70+ or 4.x (4.x is used on PHP 8.5) |
| DomPDF wrapper | 0.8 | 3.x |
| Intervention Image | 2.x + imagecache | 3.x |
| doctrine/dbal | required | not needed |

Laravel 11 is not supported: every Laravel 11 release has unpatched security advisories and Composer refuses to install it.

## Steps

1. Upgrade your application to Laravel 12 or 13 first (follow the Laravel upgrade guides).
2. Swap the package:
   ```bash
   composer remove crocodicstudio/crudbooster
   composer require vicheanath/crudbooster:^7.0
   ```
3. Run the updater. It publishes assets, migrates, and seeds new settings:
   ```bash
   php artisan crudbooster:update
   ```
4. Check the items below that apply to your project.

## What may need attention

### Copied CRUDBooster classes in your app
Some 5.x projects customized CRUDBooster by copying `src/helpers/CRUDBooster.php` into the app and loading it
through `composer.json` → `autoload.files`. That copy replaces the package class, so the app gets none of the
7.0 fixes, and every module page fails with `getDoctrineSchemaManager does not exist`. Remove the copy from
`autoload.files`, delete it, and run `composer dump-autoload`. Then re-apply real customizations another way.
For example, a SweetAlert2 delete dialog now only needs the `SWEETALERT2_DELETE_OPTIONS` config key.

### Uploads and the storage disk
CRUDBooster now resolves every upload through `Storage::path()` on the `local` disk instead of a hard-coded
`storage/app`. Projects upgraded from older Laravel versions usually keep `'root' => storage_path('app')` and need
nothing. If your `config/filesystems.php` points `local` at `storage/app/private` (the Laravel 11+ default), either
move `storage/app/uploads*` into `storage/app/private/`, or set the local root back to `storage_path('app')`.

### Image manipulation in your own code
The `Image` alias now wraps Intervention Image v3. `Image::make($path)` still loads an image, but the v2
manipulation API is gone. Use `Image::read($path)` with `cover()` (was `fit()`), `scale()` (was `resize()` with
`aspectRatio()`), and `save($path, quality: 80)`.

### Push notifications (FCM)
Google shut down the legacy FCM API and server keys. `CRUDBooster::sendFCM($tokens, $data)` now uses FCM HTTP v1.
Paste a Firebase service account JSON into **Settings → Google FCM Service Account (JSON)**, or set
`CB_FCM_SERVICE_ACCOUNT=/path/to/service-account.json`. The return value keeps the legacy shape
(`{"success": n, "failure": n, "results": [...]}`).

### Email settings
SMTP settings saved in CRUDBooster were silently ignored on Laravel 7+. With **Mail Driver = smtp** they are now
applied. With `mail` (the default) your app's own mail configuration is used, as before.

### Redirects end the request with an exception
`CRUDBooster::redirect()`, `redirectBack()`, validation failures and API auth errors used to `exit`. They now throw
`Illuminate\Http\Exceptions\HttpResponseException`, which Laravel turns into the same response. If your hooks wrap
these calls in `try { ... } catch (\Exception $e)`, rethrow `HttpResponseException`.

### PHP 8 notice compatibility
PHP 8 made undefined array keys and properties warnings, and Laravel throws those as exceptions. CRUDBooster and
generated modules rely on the PHP 7 behaviour, so these warnings are ignored inside CRUDBooster's code, compiled views
and `app/Http/Controllers`. Disable with `CB_LEGACY_NOTICE_COMPAT=false` to surface them.

### Other behaviour changes
- **API tokens:** these now really last 2880 minutes (48h). They used to expire after 2880 seconds.
- **Seeder:** `CBSeeder` is namespaced `crocodicstudio\crudbooster\database\seeds\CBSeeder`.
  `php artisan db:seed --class=CBSeeder` still works.
- **PDF:** the `PDF` alias points at `Barryvdh\DomPDF\Facade\Pdf`.
- **Laravel Excel 4:** custom export/import classes must use the typed interface signatures, for example
  `public function view(): View`.
