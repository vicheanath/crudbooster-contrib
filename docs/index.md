---
layout: home

hero:
  name: CRUDBooster
  text: Laravel CRUD generator
  tagline: The CRUDBooster you know, maintained for PHP 8.2–8.5 and Laravel 12/13, with a code-first resource layer for new screens.
  actions:
    - theme: brand
      text: Get started
      link: /en/installation
    - theme: alt
      text: Migrate to resources
      link: /en/deprecations
    - theme: alt
      text: Upgrade from 5.x
      link: /en/upgrade
    - theme: alt
      text: View on GitHub
      link: https://github.com/vicheanath/crudbooster-contrib

features:
  - title: Resources and pages
    details: One PHP class per Eloquent model gives a list, filters, actions, forms, validation and permissions. Generate it with crudbooster:make-resource.
    link: /en/panel
    linkText: Read the guide
  - title: Easy migration
    details: Classic modules, CRUDBooster::* helpers and the Module Generator are deprecated but keep working in 7.x. Every API names its replacement.
    link: /en/deprecations
    linkText: See the replacements
  - title: Drop-in upgrade
    details: Same crocodicstudio\crudbooster namespace, cbInit() controllers, hooks and cms_* tables. Existing 5.x modules keep working.
  - title: Batteries included
    details: Privileges, menus, settings, API generator with token auth, statistic dashboards, email templates and push notifications.
  - title: Tested on modern stacks
    details: About 90 feature tests run on PHP 8.2–8.5 × Laravel 12/13 and MySQL in CI.
---

::: warning Classic modules are deprecated
`CBController` modules, the `CRUDBooster::*` helpers and the Module and API generators are deprecated since 7.2.
They keep working in every 7.x release, so nothing breaks, but new screens should be
[resources and pages](/en/panel). [Deprecated APIs and How To Replace Them](/en/deprecations) maps each classic
API to its replacement.
:::

## Install

```bash
composer require vicheanath/crudbooster:^7.0
php artisan crudbooster:install
```

Then open `/admin/login`. Continue with the [installation guide](/en/installation) or the [table of contents](/en/).
