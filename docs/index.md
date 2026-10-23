---
layout: home

hero:
  name: CRUDBooster
  text: Laravel CRUD generator
  tagline: The classic CRUDBooster you know, maintained for PHP 8.2–8.5 and Laravel 12/13.
  actions:
    - theme: brand
      text: Get started
      link: /en/installation
    - theme: alt
      text: Upgrade from 5.x
      link: /en/upgrade
    - theme: alt
      text: View on GitHub
      link: https://github.com/vicheanath/crudbooster-contrib

features:
  - title: Module generator
    details: Build a full CRUD module (list, add, edit, detail, delete, import, export) from a table in a few clicks.
  - title: Drop-in upgrade
    details: Same crocodicstudio\crudbooster namespace, cbInit() controllers, hooks and cms_* tables. Existing 5.x modules keep working.
  - title: Batteries included
    details: Privileges, menus, settings, API generator with token auth, statistic dashboards, email templates and push notifications.
  - title: Tested on modern stacks
    details: About 90 feature tests run on PHP 8.2–8.5 × Laravel 12/13 and MySQL in CI.
---

## Install

```bash
composer require vicheanath/crudbooster:^7.0
php artisan crudbooster:install
```

Then open `/admin/login`. Continue with the [installation guide](/en/installation) or the [table of contents](/en/).
