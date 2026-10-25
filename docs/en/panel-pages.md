# Custom Pages

Not every screen is a list of records. A **page** is an admin screen with your own Blade view, such
as a report, a settings form or an import tool. It gets the CRUDBooster layout, a sidebar link,
permissions and a named route, without a controller or a route file.

```bash
php artisan crudbooster:make-page SalesReport
```

This creates `app/Admin/Pages/SalesReport.php` and
`resources/views/admin/pages/sales-report.blade.php`. The page opens at `/admin/sales-report`.

```php
namespace App\Admin\Pages;

use App\Services\OrderStats;
use CrudBooster\Pages\Page;
use Illuminate\Http\Request;

class SalesReport extends Page
{
    protected static ?string $navigationIcon = 'fa fa-line-chart';

    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'admin.pages.sales-report';

    // Variables for the view. Arguments are injected from the container.
    public function data(OrderStats $stats, Request $request): array
    {
        return ['total' => $stats->total($request->query('month'))];
    }

    // Optional: a page with submit() also accepts POST requests to its URL.
    public function submit(Request $request)
    {
        $request->validate(['target' => 'required|numeric']);
        // ...

        return back()->with(['message' => 'Target saved.', 'message_type' => 'success']);
    }
}
```

The view contains only the page body. It gets the variables from `data()` and `$page`:

```blade
<div class="card">
    <div class="card-body">Total this month: {{ $total }}</div>
</div>

<form method="POST" action="{{ \App\Admin\Pages\SalesReport::url() }}">
    @csrf
    <input name="target" class="form-control">
    <button class="btn btn-primary mt-2">Save</button>
</form>
```

| Property | Default |
| --- | --- |
| `$slug` | `sales-report` (kebab case of the class name) |
| `$title` | `Sales Report` |
| `$view` | required |
| `$navigationIcon`, `$navigationGroup`, `$navigationSort` | `fa fa-file-o`, none, `0` |
| `$shouldRegisterNavigation` | `true` |

`SalesReport::url()` links to the page. The route is named `cb.pages.sales-report`.

## Who can open a page

Superadmins can always open a page. Other admins need the view privilege (`is_read`) on the
module whose path is the page's slug. Run `php artisan crudbooster:sync-panel` to list pages on
**Privileges > Edit**. To use another rule, override `can()`:

```php
public function can(): bool
{
    return CRUDBooster::myPrivilegeName() === 'Accounting';
}
```
