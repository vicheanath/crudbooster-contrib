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
