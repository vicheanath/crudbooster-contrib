# Welcome To CRUDBooster

CRUDBooster is CRUD Generator for laravel, with the most important features web application development. It's easy, flexible, and powerful.

## System Requirement and Basic Technical Knowledge
- Web Server as:
  - Apache 2.4.x or higher with rewrite engine on (mod_rewrite)  
  - Nginx 1.11.x or higher
- Database that laravel supports. MySQL / MariaDB is the primary target; PostgreSQL and SQLite also work.
- Composer 2
- Laravel 12.* / 13.*
- PHP 8.2 or higher (8.3+ for Laravel 13) and the extensions:
  - OpenSSL
  - Mbstring
  - Tokenizer
  - FileInfo
  - GD or Imagick (image resize and thumbnails)

> Upgrading an existing project from `crocodicstudio/crudbooster` 5.x? See [UPGRADE.md](../../UPGRADE.md).

## Installation

0. Please make sure you have install laravel project, please follow [https://laravel.com/docs/installation](https://laravel.com/docs/installation)

1. Open the terminal, navigate to your laravel project directory.
```php
$ composer require vicheanath/crudbooster:^7.0
```

2. Setting the database configuration, open .env file at project root directory
```
DB_DATABASE=**your_db_name**
DB_USERNAME=**your_db_user**
DB_PASSWORD=**password**
```

3. Run the following command at the terminal
```php
$ php artisan crudbooster:install
```

4. Uploads are stored on Laravel's `local` disk. Fresh Laravel 11+ apps point it at `storage/app/private`; CRUDBooster follows whatever root you configure in `config/filesystems.php`.

## Backend URL
```php
/admin/login
```
- default email : admin@crudbooster.com
- default password : 123456

## What's Next
- [How To Create A Module (CRUD)](./how-to-create-module.md)

## Table Of Contents
- [Back To Index](./index.md)
