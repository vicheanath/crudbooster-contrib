# Changelog

## Unreleased

### Tailwind CSS + shadcn/ui theme for resources and pages
A second look for the resource layer, next to AdminLTE: `CB_PANEL_THEME=tailwind` (or `Panel::theme('tailwind')`).
Classic modules keep the AdminLTE layout, and the default theme is unchanged. Guide: `docs/en/panel-themes.md`.
- Styled as **shadcn/ui** with the `base-nova` preset: Base UI primitives, the Nova style, shadcn's neutral design
  tokens (light and dark), Lucide icons and the Geist font. `components.json` lets contributors run
  `npx shadcn add`; the components in `core/resources/js/components/ui` are shadcn's own, as the CLI writes them.
- Written in **TypeScript** (strict). Field props are typed after `Field::getComponentProps()`.
- A layout with its own sidebar (Menu Management menus, panel links, superadmin tools), top bar, dark mode
  (light / dark / system, without a transition flash) and a mobile menu.
- Form inputs are React components mounted on server-rendered "islands": shadcn's Combobox (search, keyboard
  navigation, chips for multiple values), a drag-and-drop upload with a canvas preview and size check, input
  groups with prefixes, suffixes and password reveal, Switch, RadioGroup and Checkbox cards. Each island contains
  plain HTML that submits the same names, so validation, old input and saving are unchanged, and forms work
  without JavaScript.
- AlertDialog confirmations instead of `window.confirm()`, and a DropdownMenu of bulk actions that counts the
  selected rows.
- Built with Vite (`npm run panel:build`) into `src/assets/panel`, which is committed and published with the
  `cb_asset` tag, so installs need no Node.js. The layout warns when the published copy is missing or stale.
- Theme-independent page components: `<x-cb::card>`, `<x-cb::stat>`, `<x-cb::grid>`, `<x-cb::table>`,
  `<x-cb::button>`, `<x-cb::badge>`, `<x-cb::alert>`.
- Extending: custom field types name a React component (`$component`, `getComponentProps()`), scripts register
  components with `CrudBooster.register()` or `registerMount()`, colours and radius are shadcn's CSS variables,
  and `CrudBooster\Themes\Theme` lets you write your own theme.
- The superadmin sidebar menu moved into `CrudBooster\Navigation\SuperadminMenu`, shared by both layouts.
- Fixed: switching the theme after views were rendered kept serving the old theme's views.
- Tests: the whole resource test suite also runs on the Tailwind theme (`PanelTailwindThemeTest`), plus 20
  Vitest component tests, and a CI job that typechecks, tests and checks the committed build matches its sources.

### Deprecated: the classic module layer (no runtime change)
`@deprecated` tags, each naming its replacement, on the classic APIs that resources, pages, the service contracts or
Laravel replace. Everything keeps working in 7.x; removal is possible in 8.0 at the earliest. Migration guide:
`docs/en/deprecations.md`.
- `CBController` (and its hooks), `modern\CrudController`, `CrudDefinition`, `Column`, `Field` and `CrudConfig`:
  use a resource.
- Every `CRUDBooster` method and the `CB` alias: use the service contracts (`AuthenticationServiceInterface`,
  `SettingsRepositoryInterface`, `FileUploaderInterface`, ...), `Resource::can()` / `Authorizer`, or Laravel.
- Global helpers `cbLang()`, `get_setting()`, `g()`, `db()`, `str_random()`, `str_slug()`, `str_limit()`,
  `ends_with()`, `extract_unit()`, `assetThumbnail()`, `assetResize()`, `min_var_export()`, `rrmdir()`, `now()`.
- Classic extension points: `actions\*`, `forms\*` and the field handlers, `grid\*`, `export\*` and their contracts.
- Module Generator, API Generator (`ApiController`, `ApiCustomController`, `ModuleGenerator`, `ApiGenerator`),
  Statistic Builder, and the `Image` alias (`CBImage`).
- Not deprecated: models, migrations, middlewares, the built-in admin screens, `CBRouter`, `CBHook`, `FCM` and the
  shared service contracts.
- The AdminLTE resource layout and `ResourceController` no longer call `CRUDBooster::adminPath()` or `cbLang()`.
  The Tailwind theme views and `SuperadminMenu` still do.
- Docs site: every classic page opens with a "Deprecated since 7.2" notice naming its replacement, with links to
  the migration guide and the new guide (`deprecated:` frontmatter, shown by `docs/.vitepress/theme`). The sidebar
  groups those pages under a collapsed "Classic Modules (deprecated)", and the home page and nav link to the guide.
- New guide `docs/en/migration-examples.md`: a module migrated in six steps (keeping its URL and privileges), then
  before/after code for columns, filters, form types, hooks, buttons, exports, sub modules, render hooks, pages,
  helpers and API endpoints. Each classic page's "How to migrate" link opens its matching example.

### Resources and pages: a code-first admin layer (no changes needed in existing modules)
A second way to build admin screens, next to classic modules. It lives in the new `CrudBooster\` namespace
(`core/`), works with Eloquent models, and is defined in code instead of in the Module Generator. Guides:
`docs/en/panel.md`, `docs/en/panel-pages.md`, `docs/en/panel-extending.md`.
- **Resources** (`CrudBooster\Resources\Resource`): one class per model gives a list with search, filters,
  sorting, pagination and row and bulk actions, plus create, edit, detail and delete pages, validation, a
  sidebar link and audit log entries. Lifecycle hooks (`beforeCreate()`, `afterUpdate()`, ...) replace `hook_*()`.
- **Tables:** `TextColumn`, `BadgeColumn`, `BooleanColumn` and `ImageColumn` with relationship paths
  (`category.name`, eager loaded); `SelectFilter`, `TernaryFilter` and `Filter`; `Action` and `BulkAction`.
- **Forms:** `TextInput`, `Textarea`, `Select` (options, relationships, many-to-many sync), `Radio`,
  `CheckboxList`, `Toggle`, `DatePicker`, `FileUpload`, `Hidden`, `Placeholder`, `ViewField` and `Section`.
  Validation rules come from the fields, and only the fields in the form are saved.
- **Pages** (`CrudBooster\Pages\Page`): custom screens in the admin layout with injected `data()` and an
  optional `submit()`.
- **Permissions:** a model policy if there is one, otherwise the existing privilege matrix.
  `php artisan crudbooster:sync-panel` lists resources and pages on Privileges > Edit. Replace the whole policy
  by binding `CrudBooster\Auth\Authorizer`.
- **Extending:** custom field, column and filter classes, plugins (`CrudBooster\Plugins\Plugin`), render hooks
  and sidebar links through the `CrudBooster\Facades\Panel` facade.
- **Commands:** `crudbooster:make-resource` (with `--generate` to read the table), `crudbooster:make-page`.
- Classes in `app/Admin/Resources` and `app/Admin/Pages` are discovered; see the new `PANEL` config key.
- The admin layout accepts a `page_header` section and the `head` / `body.end` render hooks; the sidebar lists
  panel links after the Menu Management menus. Classic module pages render as before.
- Playground: `Products` and `Reviews` resources and a `Store Overview` page, under "Catalog (resources)", on
  the same tables as the classic shop modules.
- 29 new tests (`tests/Feature/PanelTest.php`, `PanelCommandsTest.php`, `tests/Unit/Panel`).

## v7.1.0

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

- Playground: a **Field Showcase** module with every form field type and lookup variant on one form, and a
  seeded record with every field filled in.

### AdminLTE 4 admin theme
The admin UI moves from AdminLTE 2.3.8 (Bootstrap 3.4) to **AdminLTE 4.10 (Bootstrap 5.3)**. Run
`php artisan crudbooster:update` to publish the new assets. Modules, hooks and custom views keep working:
- New layout (`app-wrapper`, `app-header`, `app-sidebar`, `app-main`, `app-footer`) in `admin_template`, `header`,
  `sidebar` and `footer`. Element ids and the old hook classes (`content-wrapper`, `content-header`,
  `#content_section`, `main-header`, `main-sidebar`, `notifications-menu`) stay on the new elements.
- Each privilege's skin (`skin-blue`, `skin-red-light`, ...) still colours the header and sidebar brand; `-light`
  skins get the light sidebar. The theme preview on the privilege form no longer wipes the layout classes.
- Bootstrap 3 / AdminLTE 2 compatibility layer for views written against the old markup:
  `assets/css/bs3-compat.css` (`box`, `panel`, `col-xs-*`, offsets, `form-horizontal`, `btn-default`, `btn-flat`,
  `btn-xs`, `btn-block`, `label-*`, `input-group-addon`/`-btn`, `pull-*`, `hidden-*`, `help-block`, `has-error`,
  `dropdown-menu > li > a`, `nav-tabs > li.active`, `well`, `media`, glyphicons, AdminLTE 2 `bg-*`/`text-*` colours)
  and `assets/js/bs3-compat.js` (`data-toggle`/`data-target`/`data-dismiss` mirrored to `data-bs-*`, `.in` to
  `.show`, `$(el).modal()` and the other jQuery plugin calls available from page load, `.box` collapse/remove).
- Login, forgot password, lock screen and 404 pages, the statistic builder, the datamodal browser and the public
  API documentation use AdminLTE 4 too, through the new `partials.theme_styles` and `partials.theme_scripts`.
  The public API page and the login pages no longer load Bootstrap or Font Awesome from CDNs.
- The WYSIWYG field and the API generator use Summernote Lite (same editor and API, no Bootstrap 3 dependency).
- Grid and datamodal pagination render with Laravel's Bootstrap pagination view, whatever the app's default.
- The admin stays in light mode: AdminLTE 4 would otherwise follow the OS dark-mode setting.
- jQuery 2.2.3, Font Awesome 4.7, Ionicons and the date, time, select2, DataTables and lightbox plugins are
  unchanged, so stored `fa fa-*` module and menu icons and custom scripts keep working. The AdminLTE 2 files stay
  in `assets/adminlte` for custom views that load its plugins.

### Native AdminLTE 4 markup in every view
The remaining views no longer rely on the Bootstrap 3 compatibility layer (it stays for apps' own views):
- `box` and `panel` wrappers are AdminLTE 4 cards (`card`, `card-header`, `card-body`, `card-footer`, `card-tools`,
  `card-primary card-outline`, ...): forms, detail, child rows, import, menu management, privileges, settings,
  module generator, API generator and documentation, and the statistic builder widgets.
- Tables use Bootstrap 5 classes (`table-sm`, `table-active`, `table-info`, ...), labels are `badge text-bg-*`,
  help text is `form-text`, input add-ons are `input-group-text`, `pull-*` is `float-*`, and modals use `btn-close`
  and `data-bs-*` attributes. Nav tabs use `nav-item` / `nav-link`.
- Sidebar: the brand shows the favicon setting, or the app name's first letter, next to the name (it stays when the
  sidebar collapses), and the user panel follows the sidebar's light or dark theme.
- The section-header toggle and the statistic builder's drag handles select the new card classes as well as the
  old ones, so custom forms built on `box-body` keep working.
- Fixed: on the detail page, every cell of a child table was bold; the menu management form squeezed its labels
  into a narrow column (it now stacks them, via the new `cb-form-stacked` form class).

### Data grid
The module index page (`default/index`, `default/table`) is rebuilt on Bootstrap 5 / AdminLTE 4 markup:
- One toolbar: bulk actions, search (press `/` to focus it), the filter button with a count of active filters, and a
  "Show" page-size picker. It wraps on small screens.
- Active search, filters and sorting appear as chips; each one removes itself, and "Clear all" removes them all.
- Selecting rows: click anywhere on a row, shift-click for a range, a live "N selected" count, an indeterminate
  "select all" box, and highlighted selected rows. Bulk actions stay disabled until a row is selected, and the
  confirmation says how many rows it applies to.
- Sort headers show the current direction (and set `aria-sort`); the duplicate header row under the table is gone.
- An empty grid says why and offers "Clear all" (when filtered) or "Add Data".
- Row counts and pagination sit in the card footer. The filter and export dialogs use Bootstrap 5 forms; the export
  dialog can tick or clear every column at once.
- Row action dropdowns (`button_action_style = 'dropdown'`) are no longer cut off by the scrolling table.
- Every id, class, field name and hook the grid had is kept (`#table_dashboard`, `#form-table`, `.checkbox`,
  `.selected-action`, `#btn_advanced_filter`, `#advanced_filter_modal`, `#export-data`, `table_row_color`,
  `hook_row_index`, ...).
- Fixed: a column's `style` was printed as the text "1" on the wrong cell (removed Blade `or` syntax, and the bulk
  and numbering cells were not counted); the filter dialog offered no comparison operators for SQLite/PostgreSQL
  `INTEGER`/`numeric` columns; the export dialog's script was bound twice.

### Security: front-end libraries updated
Libraries with published advisories are updated to the newest release that works with the bundled plugins:
- jQuery 2.2.3 → **3.7.1**, with jQuery Migrate 3.6.0 (muted) for jQuery 1/2 APIs that older plugins and custom
  scripts still call. Not 4.0, which removes APIs the date and time pickers use.
- Moment 2.13.0 → **2.31.0** (ReDoS fixes), same path.
- DataTables 1.10.7 → **2.3.8** with Bootstrap 5 styling, in `assets/datatables`.
- Select2 4.0.2 → **4.1.0**, Lightbox2 2.9.0 → **2.12.0**, Summernote (classic build) 0.8.1 → **0.9.1**, all at
  their old paths so custom pages that load them get the fixes too.
- Statistic builder: jQuery UI 1.11.4 and Raphaël 2.1.0 from CDNs → **1.14.2** and **2.3.0**, bundled.
- Docs site: Vite is pinned to 6.4.5 (`overrides` in `package.json`) because VitePress 1.6.4 asks for Vite 5, which
  has no patched release. `npm audit` reports no vulnerabilities.

### Fixed (front end)
- The grid's bulk-action confirmation never opened: a line break inside a JavaScript string stopped the whole
  script, which also held the responsive-table toggle.
- The statistic builder's widget panel came back empty after dropping a widget.

### Changed
- A `header` field's `'collapsed' => true` now starts its section folded and `false` keeps it open; both were
  inverted. Sections without the option still start open.
- Saving a record, its child rows and its many-to-many links is one database transaction: when one fails,
  nothing is kept. `hook_after_add`/`hook_after_edit` run after the commit.
- Header and sidebar typography: one font stack, a light header shadow, uppercase section labels, tighter menu items
  and ellipsis for long names. Colours still come from each privilege's skin. Run `php artisan crudbooster:update`
  to republish the CSS.
- The navbar, user menu and sidebar panel show the admin's initials instead of the profile photo, in AdminLTE
  2.3.8's avatar slots and sizes.

### Fixed
- Found with the playground's Field Showcase module:
  - A `money` field with cents (`decimals` or `priceformat_parameters.centsLimit`) saved "$ 2,345.67" as 234567,
    and its detail view rounded to whole units. The field's own format is now used for both.
  - Child rows were read with inner joins on their select and datamodal lookups, so a row with an empty lookup
    was hidden on the edit form and deleted on the next save. Unqualified columns also failed with "ambiguous
    column" when a lookup table shared a column name.
  - A child row with an empty optional column failed the whole save ("all VALUES must have the same number of
    terms").
  - In a child field, `select` accepted `dataenum` only as an array and `radio` only as a `"a;b"` string; both
    accept either now, like the top-level fields.
  - A `header` without a `name` was rendered as a hidden input, and one with a `name` was saved as a column.
  - The `json` field's editor never started (its script ran before jQuery loaded), and its detail view was empty.
    The detail view now prints the formatted JSON.
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
