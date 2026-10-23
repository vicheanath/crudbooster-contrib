# Contributing to CRUDBooster

Thanks for helping keep the classic CRUDBooster alive. This guide gets you from a fresh clone to a
running admin panel and a pull request.

## Requirements

- PHP 8.2+ with `mbstring`, `openssl`, `pdo_sqlite`, `fileinfo` and `gd` (or `imagick`)
- Composer 2
- Optional: Docker, for MySQL and a mail catcher. SQLite works without it.

## 1. Clone and install

```bash
git clone https://github.com/<your-username>/crudbooster-contrib.git
cd crudbooster-contrib
composer install
composer test
```

## 2. Start the playground

CRUDBooster is a package, so it needs a Laravel app to run in. The playground script creates one in
`playground/` (git-ignored) and installs this repository into it through a Composer **path repository with a
symlink**. Edits under `src/` show up on the next page load.

```bash
php bin/playground setup      # Laravel 13 + SQLite, publishes config/assets, migrates, seeds the admin
php bin/playground serve      # http://127.0.0.1:8000/admin/login
```

Log in with `admin@crudbooster.com` / `123456`.

| Command | What it does |
|---|---|
| `php bin/playground setup --laravel=12` | Build the playground on Laravel 12 instead |
| `php bin/playground setup --mysql --mailpit` | Use the Docker MySQL and Mailpit services (run `docker compose up -d` first) |
| `php bin/playground setup --force` | Throw the playground away and rebuild it |
| `php bin/playground fresh` | Drop all tables, migrate and re-seed the admin |
| `php bin/playground publish` | Re-copy `src/assets` into `playground/public/vendor/crudbooster` |
| `php bin/playground artisan -- route:list` | Run any artisan command inside the playground |
| `php bin/playground destroy` | Delete `playground/` |

`composer playground -- <command>` does the same thing.

### What updates live, and what doesn't
- **Live:** PHP in `src/` (controllers, helpers, middleware) and Blade views in `src/views`.
- **Re-publish:** `src/assets` (CSS/JS/images) are copied into the app. Run `php bin/playground publish`.
- **Copied once at setup:** `src/configs/crudbooster.php` → `playground/config/crudbooster.php`, and
  `src/userfiles/*` → `playground/app/Http/Controllers`. Edit the playground copies to experiment, or
  `setup --force` to start over.
- **Database changes:** after editing `src/database/migrations` or `CBSeeder`, run `php bin/playground fresh`.

Modules you create with the Module Generator are written into `playground/app/Http/Controllers`. They never
reach this repository, so try things freely.

## 3. Run the tests

```bash
composer test                 # SQLite, ~90 tests
docker compose up -d mysql
composer test:mysql           # same suite on MySQL 8.4 (CRUDBooster's main target)
vendor/bin/phpunit --filter ImportExportTest
```

- `tests/` covers single helpers (DB layer, images, FCM, smoke checks).
- `tests/Feature/` drives the admin panel over HTTP as a logged-in admin. Extend `FeatureTestCase`, call
  `$this->loginAsAdmin()`, and use `$this->admin('path')` for admin URLs.
- CI runs PHP 8.2–8.5 × Laravel 12/13 plus MySQL. Please add a test with every bug fix.

To try another Laravel version locally: `composer require "laravel/framework:^12.0" --no-update && composer update`.
Don't commit the result.

## 4. Make your change

- Branch from `main`: `git switch -c fix/short-description`.
- **Keep the 5.x architecture.** Existing projects depend on the `crocodicstudio\crudbooster` namespace,
  `cbInit()` controllers, hooks, view names and the `cms_*` tables. Don't rename or remove public helpers,
  config keys or settings. Add new ones, and note them in `UPGRADE.md` if users need to act.
- Follow the style of the surrounding code (PSR-12-ish, 4 spaces; see `.editorconfig`).
- Update `CHANGELOG.md` under an `## Unreleased` heading.
- Update `docs/en` if behaviour visible to module authors changes.

## 5. Open a pull request

Push your branch and open a PR against `main`. The template asks what changed, how you tested it, and
whether existing projects need to do anything. CI must be green before review.

## Reporting bugs

Use the **Bug report** issue template, and include the PHP, Laravel and CRUDBooster versions, the database,
and steps to reproduce. If you can, add a failing test.

## Releasing (maintainers)

1. Move `## Unreleased` in `CHANGELOG.md` to the new version and bump `$version` in
   `src/commands/CrudboosterVersionCommand.php`.
2. Merge to `main` with CI green.
3. `git tag -a vX.Y.Z -m "vX.Y.Z" && git push origin vX.Y.Z`, then create the GitHub release from the
   changelog. Packagist picks up the tag through its GitHub hook.
