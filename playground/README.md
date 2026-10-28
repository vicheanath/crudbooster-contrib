# CRUDBooster E-commerce Playground

This is a committed Laravel application used to develop and manually test
CRUDBooster. It is intentionally an e-commerce backend rather than a disposable
install-only example.

## Start the playground

From the repository root, initialize the committed application:

```bash
cd playground
composer install
cp -n .env.example .env
php artisan key:generate
php artisan migrate --seed --force
php artisan serve
```

The admin login is:

```text
URL:      http://127.0.0.1:8000/admin/login
Email:    admin@crudbooster.com
Password: 123456
```

## Data and modules

The playground seeds CRUDBooster's standard tables and an e-commerce catalog
with these modules:

- Categories
- Products
- Customers
- Orders
- Order items
- Product reviews
- Coupons

The **Field Showcase** module (`field_showcases`) puts every form field type on one form, each labelled
with its type: text, email, password, number, money (with cents), multitext, hidden, date, datetime,
time, textarea, wysiwyg, json, radio, select, select2 (including AJAX), datamodal, checkbox, uploads,
googlemaps, custom HTML, header sections and a child table. Lookups come from an enum, a table, a SQL
query and many-to-many pivot tables. One seeded record has every field filled in, so the grid, detail,
edit and export views can be checked at once. The `filemanager` type is left out: it has no view
component yet. The map needs the "Google API Key" setting.

The generated modules exercise common CRUDBooster features including joined
fields, validation, select/select2/radio/checkbox controls, money and numeric
fields, date and datetime controls, uploads, image previews, textareas,
WYSIWYG content, filtering, exports, details, and CRUD actions.

## Resource examples (HR, CRM, e-commerce)

Next to the classic modules, `app/Admin` holds example admin screens built with
[resources and pages](../docs/en/panel.md). They only use the components that ship with CRUDBooster
(columns, filters, form fields, row and bulk actions, hooks and the `<x-cb::...>` page components), so
you can copy from them. Each system has a dashboard page and its own sidebar group.

| Group | Screens | What they show |
| --- | --- | --- |
| **E-commerce (resources)** | Store Overview, Categories, Products, Reviews, Customers, Orders, Order items, Coupons | Order status workflow (row actions that only appear when they apply), CSV export, order items as a child resource whose hooks keep the order total in sync, lifetime spend with `withSum()` |
| **HR** | HR Dashboard, Departments, Employees, Leave requests, Skills | A manager relationship to the same table, skills as many-to-many, photo uploads, form sections, approve / reject with confirmation, date validation, "away today" on the dashboard |
| **CRM** | Sales Pipeline, Companies, Contacts, Deals, Activities, Tags | Owners defaulting to the logged-in admin (an injected service), tags as checkboxes, a pipeline with won / lost actions, overdue and "my deals" filters, a weighted forecast |

The resources are in `app/Admin/Resources/{Shop,Hr,Crm}` (plus `ProductResource` and `ReviewResource`),
the pages in `app/Admin/Pages`, and their tables in the `2026_01_03` and `2026_01_04` migrations.
`HrSeeder`, `CrmSeeder` and `ShopDemoSeeder` fill them with demo data. Parent screens link to their
children with filtered URLs, e.g. a department's **Employees** button opens
`/admin/hr-employees?filters[department]=1`.

`tests/Feature/AdminExamplesTest.php` opens every page of every resource and runs the main actions.

## Development commands

```bash
cd playground
php artisan migrate --seed --force
php artisan route:list
php artisan test
```

The seeders are idempotent and can be run repeatedly while developing. Use
`php artisan migrate:fresh --seed --force` only when a clean database is
required.
