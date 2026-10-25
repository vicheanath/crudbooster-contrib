I have several projects built with **CRUDbooster**, but the original project is no longer actively maintained, and the original maintainer has released a new version with a completely different architecture from the old version my projects rely on. As a result, migrating to the new version would require significant changes to my existing codebase.

# :rocket: CRUDBOOSTER - Laravel CRUD Generator

[![Latest Stable Version](https://poser.pugx.org/vicheanath/crudbooster/v/stable)](https://packagist.org/packages/vicheanath/crudbooster)
[![Tests](https://github.com/vicheanath/crudbooster-contrib/actions/workflows/tests.yml/badge.svg)](https://github.com/vicheanath/crudbooster-contrib/actions/workflows/tests.yml)
[![License](https://poser.pugx.org/vicheanath/crudbooster/license)](https://packagist.org/packages/vicheanath/crudbooster)
[![Docs](https://img.shields.io/badge/docs-online-blue)](https://vicheanath.github.io/crudbooster-contrib/)

> Laravel CRUD Generator, Make a Web Application Just In Minutes, Even With Less Code and fewer Steps !

# About CRUDBooster
CRUDBooster is a Laravel CRUD Generator. With a new concept of scaffolding, very easy for the beginner and professional. It's easy to use, easy to custom, and very flexible. Don't limit your mind, CRUDBooster gives us a true flexibility to develop an app. You can utilize its built in CRUD Generator or even you just need for your master data only.

## This Fork
`vicheanath/crudbooster` keeps the classic CRUDBooster 5.x architecture and runs it on **PHP 8.2 – 8.5** and **Laravel 12 / 13**.
The namespace is unchanged, so existing modules keep working.

```bash
composer require vicheanath/crudbooster:^7.0
php artisan crudbooster:install
```

Coming from `crocodicstudio/crudbooster` 5.x? Read [UPGRADE.md](UPGRADE.md). Release notes are in [CHANGELOG.md](CHANGELOG.md).

### Two ways to build screens
- **Classic modules**: generate a CRUD from a table with the Module Generator and customise it in `cbInit()`.
  Unchanged from 5.x.
- **Resources and pages**: code-first classes on Eloquent models, with typed columns, fields, filters and actions,
  policies, plugins and generators. See [Resources: Code-First Admin Pages](/docs/en/panel.md).

```php
class ProductResource extends Resource
{
    protected static string $model = Product::class;

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('category.name'),
            TextColumn::make('price')->money('$'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            Select::make('category_id')->relationship('category', 'name'),
            TextInput::make('price')->numeric(),
        ]);
    }
}
```

Both use the same login, layout, privileges and audit log, so they can run side by side in one app.
Resources and pages can use the classic AdminLTE look or a **Tailwind CSS theme built with shadcn/ui
(Base UI) and TypeScript**, with dark mode (`CB_PANEL_THEME=tailwind`, see [Themes](/docs/en/panel-themes.md)).

## Let's Get Started
📖 **Documentation site: https://vicheanath.github.io/crudbooster-contrib/**

### [Installation](/docs/en/installation.md)
### [Documentation](/docs/en/index.md)

## Support
Issues with this fork go to [GitHub Issues](https://github.com/vicheanath/crudbooster-contrib/issues). To request help or report a problem, centralize everything there. Follow the guidelines in the "Using CRUDBooster Issues Page" session.

## Important Note To Developer
Dear Developer, you are not end new user, to use CRUDBooster, **Laravel knowledge** is a must!, CRUDBooster is just an additional package **Laravel Framework** to help use more efficient in development. If you dont have a laravel knowledge, so nightmare will come to you :D. We can't help you as you want in every issues especially basic issues of web development instead you can find out in internet (stackoverflow or any other)

## Contribution

All ways to contribute are welcome. [CONTRIBUTING.md](CONTRIBUTING.md) covers the local playground (a Laravel app wired to this repository), running the tests, and opening a pull request. The short version:

```bash
composer install
cd playground && composer install
cd playground && php artisan migrate --seed --force
cd playground && php artisan serve   # http://127.0.0.1:8000/admin/login
composer test
```

#### Attention

- When you submit your code, you are automatically accepting that it will not be exclusively yours, and that CRUDBooster may use it in any context, as it deems appropriate.
- If you use a third-party code, it must be licensed as open-source.
- Contributions will only be accepted through a Pull Request.
- Your contribution can not contain execution errors.

## Using GitHub Issues Page

Do you have any doubt? Are you having some difficulty? Did you find a translation error, a bug, or any problem? Before posting, search the [issues](https://github.com/vicheanath/crudbooster-contrib/issues) and make sure if anyone has not reported the same or similar problem.

To post you should detail the most about what happened. Specify in detail:

- Operating system (name, version and architecture)
- Web server (name, version and architecture)
- Browser (name, version and architecture)
- CRUDBooster version
- Laravel version
- What is the problem?
- How should it happen? What was the expected behavior?
- Tell how to reproduce the problem step by step
- If possible, take screenshots or send snippets of code

No one is obligated to understand you. Then explain your problem with clarity, detail and precision. Make yourself understandable. Once the problem is resolved, remember to thank those who helped and close the issue. 

If there is no interaction for a long period of time, it will be assumed that it has been resolved or that it is irrelevant, and may be closed without notice. Feel free to reopen it if you think it is really necessary.

## Credits
1. Laravel Export HTML to Excel by [Maatwebsite](https://github.com/Maatwebsite/Laravel-Excel)
2. Laravel DOM PDF by [Barryvdh](https://github.com/barryvdh/laravel-dompdf)
3. Laravel Widgetize [ImanGhafoori](https://github.com/imanghafoori1/laravel-widgetize)
4. Admin Theme by [AdminLTE 4](https://adminlte.io) (Colorlib, MIT)
5. Laravel Framework by [Taylor Otwell](https://github.com/laravel/laravel)
6. Thanks To All [Developers](https://github.com/crocodic-studio/crudbooster/graphs/contributors)
7. Thanks To All Funders

## Support & Donation
Hi thanks for using my open source project, you could support me via :
[https://saweria.co/ferryariawan](https://saweria.co/ferryariawan)
or via [https://buymeacoffee.com/ferryariawan](https://buymeacoffee.com/ferryariawan)

## Contact Us For Business
Email : crudbooster@gmail.com

## License 
CRUDBooster is under [MIT License](https://opensource.org/licenses/MIT)


### :star: Your Stars Make Us Do More :star:
As always if you found this package useful and you want to encourage us to maintain and work on it. Just press the star button to declare your willing.
