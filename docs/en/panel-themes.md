# Themes: AdminLTE Or Tailwind + shadcn/ui

Resource and page screens come in two looks:

| Theme | What it is |
| --- | --- |
| `adminlte` (default) | The classic CRUDBooster layout: AdminLTE 4 and Bootstrap 5. |
| `tailwind` | A Tailwind CSS layout styled as [shadcn/ui](https://ui.shadcn.com) (the `base-nova` preset: [Base UI](https://base-ui.com) primitives, the Nova style, neutral colours, Lucide icons, the Geist font). Form inputs and table controls are React components written in TypeScript, with dark mode, a searchable combobox, a drag-and-drop upload and confirmation dialogs. |

The theme only changes how screens look. Resources, pages, fields, columns, filters, actions,
permissions, hooks and plugins are the same code in both. **Classic `CBController` modules always use
the AdminLTE layout**, and both layouts' sidebars list the same menus, resources, pages and
superadmin tools, so an admin can move between the two.

## Switching

```dotenv
CB_PANEL_THEME=tailwind
```

or in `config/crudbooster.php` (`'PANEL' => ['theme' => 'tailwind', ...]`), or from a service
provider's `boot()`:

```php
use CrudBooster\Facades\Panel;

Panel::theme('tailwind');
```

The Tailwind theme's compiled CSS and JavaScript ship with the package, so you don't need
Node.js. Publish them like CRUDBooster's other assets, and again after each update:

```bash
php artisan vendor:publish --tag=cb_asset --force   # or: php artisan crudbooster:update
```

If they are missing or out of date, the layout shows a banner with this command.

## What the Tailwind theme does

- **Inputs are React "islands".** Each field renders `<div data-cb-component="Select" data-cb-props="{...}">`
  with plain HTML inside it, and the script swaps in the React component. The component submits the same
  input names the plain HTML does, so validation, old input, error messages and saving all work as
  before, and the form still works if the script is blocked.
- **The components are shadcn/ui's**, built on Base UI's accessible primitives: Select is shadcn's
  Combobox (searchable, keyboard navigation, chips for `multiple()`), Toggle is its Switch, Radio and
  CheckboxList use RadioGroup and Checkbox, confirmations are AlertDialog, the bulk menu is
  DropdownMenu and the theme switch is a ToggleGroup. The server-rendered parts (layout, table, cards,
  plain inputs) use the same Nova classes and design tokens, so the whole page looks like one shadcn app.
- **FileUpload** has drag and drop, an image preview, a client-side size check (`maxSize()`) and a
  Remove/Undo button for the current file.
- **TextInput** shows its `prefix()`/`suffix()` and a show/hide button for passwords. **Toggle** is a switch,
  and **Radio** and **CheckboxList** are option cards.
- Delete buttons and actions with `requiresConfirmation()` open a dialog instead of `window.confirm()`.
- The bulk action menu counts the selected rows.
- **Dark mode** follows the system setting, or the light/dark/system switch in the top bar (kept in
  `localStorage`).
- The **sidebar** collapses into a menu button on small screens.

### Colours and radius

The theme uses shadcn's design tokens (`--background`, `--foreground`, `--primary`, `--muted`,
`--border`, `--radius`, …, with a `.dark` set). Override any of them to rebrand without rebuilding, for
example with a theme from [ui.shadcn.com/themes](https://ui.shadcn.com/themes):

```php
Panel::renderHook('head', fn () => '<style>
    :root { --primary: oklch(0.546 0.245 262.881); --primary-foreground: oklch(0.985 0 0); --radius: 0.5rem; }
    .dark { --primary: oklch(0.623 0.214 259.815); --primary-foreground: oklch(0.985 0 0); }
</style>');
```

## Page components that work in both themes

A [custom page](./panel-pages.md) view written with Bootstrap classes looks wrong in the Tailwind
theme, and the reverse is also true. These Blade components render the right markup for the active theme:

```blade
<x-cb::grid cols="3">
    <x-cb::stat label="Orders today" :value="$orders" icon="fa fa-shopping-cart" color="primary" :url="OrderResource::url()" />
    <x-cb::stat label="Refunds" :value="$refunds" icon="fa fa-undo" color="danger" />
    <x-cb::stat label="Revenue" :value="'$'.number_format($revenue)" icon="fa fa-money" color="success" />
</x-cb::grid>

<x-cb::card title="Latest orders" description="The last 10 orders." flush>
    <x-slot:actions><x-cb::button :href="OrderResource::url()" size="sm">All orders</x-cb::button></x-slot:actions>
    <x-cb::table>
        <table>
            <thead><tr><th>Order</th><th>Status</th></tr></thead>
            <tbody>
            @foreach($latest as $order)
                <tr><td>{{ $order->number }}</td><td><x-cb::badge color="warning">{{ $order->status }}</x-cb::badge></td></tr>
            @endforeach
            </tbody>
        </table>
    </x-cb::table>
</x-cb::card>

<x-cb::alert type="warning" title="Heads up">Stock is synced every hour.</x-cb::alert>
```

| Component | Props |
| --- | --- |
| `x-cb::card` | `title`, `description`, `flush` (no padding); slots `actions`, `footer` |
| `x-cb::stat` | `label`, `value`, `icon`, `color`, `url`, `description` |
| `x-cb::grid` | `cols` (1–4): its children become responsive columns |
| `x-cb::table` | Styles a plain `<table>` inside it |
| `x-cb::button` | `href` (a link) or `type`, `color`, `icon`, `size="sm"` |
| `x-cb::badge` | `color` |
| `x-cb::alert` | `type` (`success`, `danger`, `warning`, `info`), `title`, `dismissible` |

Colours are `primary`, `secondary`, `success`, `danger`, `warning` and `info`.

### Your own Tailwind classes

The shipped stylesheet only contains classes used in CRUDBooster's own views. If you put other
Tailwind classes in your page views, build a small stylesheet for them with your app's Vite setup
and load it with a render hook:

```css
/* resources/css/admin.css */
@import "tailwindcss/utilities.css" layer(utilities);
@custom-variant dark (&:is(.dark *));
@source "../views/admin";
```

To use the shadcn token utilities (`bg-primary`, `text-muted-foreground`, …) in that stylesheet, copy the
`@theme inline` block from `core/resources/css/panel.css` into it.

```php
Panel::renderHook('head', fn () => app(\Illuminate\Foundation\Vite::class)(['resources/css/admin.css']));
```

## Your own React input

A field type names the component that renders it in the Tailwind theme, and
`getComponentProps()` decides what that component receives. The AdminLTE theme renders the field's
plain `$view`, so the same field class works in both:

```php
use CrudBooster\Forms\Fields\TextInput;

class ColorPicker extends TextInput
{
    protected ?string $component = 'ColorPicker';

    public function getComponentProps(): array
    {
        return parent::getComponentProps() + ['swatches' => ['#ef4444', '#f59e0b', '#10b981', '#6366f1']];
    }
}
```

Register the component from a script loaded on panel pages. It must use the page's React
(`CrudBooster.React`), since two copies of React cannot share one page. Submit the value as an input
named `props.name`:

```js
// resources/js/admin.js, loaded with Panel::renderHook('body.end', fn () => app(Vite::class)(['resources/js/admin.js']))
document.addEventListener('crudbooster:ready', ({ detail: CrudBooster }) => {
  const { createElement: h, useState } = CrudBooster.React

  CrudBooster.register('ColorPicker', function ColorPicker({ name, id, value, swatches }) {
    const [color, setColor] = useState(value || swatches[0])
    return h('div', { className: 'flex gap-2' },
      h('input', { type: 'hidden', name, value: color }),
      swatches.map((swatch) => h('button', {
        key: swatch, type: 'button', id: swatch === color ? id : undefined,
        onClick: () => setColor(swatch), 'aria-pressed': swatch === color,
        className: 'size-8 rounded-full ring-2 ring-offset-2 ' + (swatch === color ? 'ring-foreground' : 'ring-transparent'),
        style: { backgroundColor: swatch },
      })),
    )
  })
})
```

Not using React? Use `CrudBooster.registerMount('ColorPicker', (element, props) => { /* render into element */ })`.
It can return a cleanup function. Islands added to the page later are mounted with
`CrudBooster.mount(container)`, and `CrudBooster.confirm({ message })` opens the confirmation dialog
and resolves to `true` or `false`.

## shadcn/ui components

The components in `core/resources/js/components/ui` are shadcn/ui's, as `npx shadcn add` writes them for
the `base-nova` preset: you own the code, so change them freely. `components.json` at the repository root
configures the CLI (TypeScript, `@/components/ui` → `core/resources/js/components/ui`,
`core/resources/css/panel.css`), so contributors can add more:

```bash
npx shadcn@latest add popover tabs
```

The field and table islands in `core/resources/js/components` are typed with the props the PHP side
sends (`lib/types.ts`), and `npm run panel:typecheck` runs `tsc` in strict mode.

## Writing your own theme

A theme is a set of views that take precedence over the default ones, plus the HTML its layout puts
in `<head>`. A view the theme doesn't have falls back to the default (AdminLTE) view:

```php
use CrudBooster\Themes\Theme;
use Illuminate\Support\HtmlString;

class AcmeTheme extends Theme
{
    public function getName(): string
    {
        return 'acme';
    }

    public function getViewPaths(): array
    {
        return [resource_path('views/acme-admin')];   // e.g. layout.blade.php, resources/index.blade.php
    }

    public function getHeadHtml(): HtmlString
    {
        return new HtmlString('<link rel="stylesheet" href="'.asset('css/acme-admin.css').'">');
    }
}

Panel::theme(new AcmeTheme);
```

To change a few of the Tailwind theme's views, extend `TailwindTheme` and put your directory first in
`getViewPaths()`. Copy the view you want to change from `core/resources/views/themes/tailwind` into it.

## Working on the theme

The sources are in `core/resources/js` (TypeScript and React, with Vitest tests next to them) and
`core/resources/css/panel.css`. The build goes to `src/assets/panel` and is committed. CI typechecks,
runs the tests and fails if the build is out of date.

```bash
npm install
npm run panel:typecheck  # tsc --strict
npm run panel:test       # component tests
npm run panel:watch      # rebuild on change
npm run panel:build      # before committing
php artisan vendor:publish --tag=cb_asset --force   # in the playground, to see the new build
```

The playground uses the Tailwind theme (`CB_PANEL_THEME=tailwind` in `.env.example`).
