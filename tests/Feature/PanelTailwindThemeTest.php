<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\tests\fixtures\Panel\Product;
use CrudBooster\Panel\Panel;
use CrudBooster\Themes\TailwindTheme;
use CrudBooster\Themes\Theme;
use Illuminate\Support\Facades\View;

/**
 * Every PanelTest request again, with the Tailwind theme, plus what only this theme does.
 */
class PanelTailwindThemeTest extends PanelTest
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);
        $app['config']->set('crudbooster.PANEL.theme', 'tailwind');
    }

    protected function badgeMarkup(string $color): string
    {
        return TailwindTheme::badge($color);
    }

    protected function activeNavigationMarkup(string $url): string
    {
        return 'href="'.$url.'" class="cb-nav-link cb-nav-link-active"';
    }

    public function test_the_layout_loads_the_theme_assets_and_translations()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('products'))->assertOk()
            ->assertSee(asset('vendor/crudbooster/panel/panel.css').'?v=', false)
            ->assertSee('<script type="module" src="'.asset('vendor/crudbooster/panel/panel.js').'?v=', false)
            ->assertSee('id="cb-panel-config"', false)
            ->assertSee('data-cb-component="ThemeToggle"', false)
            ->assertDontSee('adminlte.min.css', false)
            // The assets are not published in the test app.
            ->assertSee('id="cb-assets-warning"', false);
    }

    public function test_form_fields_are_react_islands_with_a_plain_html_fallback()
    {
        ['hammer' => $hammer, 'red' => $red] = $this->seedProducts();
        $hammer->tags()->attach(\crocodicstudio\crudbooster\tests\fixtures\Panel\Tag::query()->where('name', 'Blue')->first());
        $this->loginAsAdmin();

        $html = $this->get($this->admin("products/{$hammer->id}/edit"))->assertOk()->getContent();

        $islands = $this->islands($html);
        $this->assertSame(['TextInput', 'TextInput', 'Select', 'Select', 'Toggle', 'Select', 'DatePicker', 'FileUpload'], array_column($islands, 'component'));

        $tags = $islands[5]['props'];
        $this->assertSame('tags', $tags['name']);
        $this->assertTrue($tags['multiple']);
        $this->assertCount(2, $tags['value']);
        $this->assertContains((string) $red->id, $tags['value']);
        $this->assertContains(['value' => (string) $red->id, 'label' => 'Red'], $tags['options'], 'Options are {value, label} pairs');

        $price = $islands[1]['props'];
        $this->assertSame(['number', '$'], [$price['type'], $price['prefix']]);
        $this->assertEquals(12.5, (float) $price['value']);
        $this->assertTrue($islands[4]['props']['value'], 'The toggle receives a boolean');
        $this->assertSame('photo__remove', $islands[7]['props']['removeName']);

        // The fallback inside each island submits the same names.
        $this->assertStringContainsString('name="tags[]"', $html);
        $this->assertStringContainsString('value="Hammer"', $html);
    }

    public function test_invalid_fields_tell_their_component()
    {
        ['tools' => $tools] = $this->seedProducts();
        $this->loginAsAdmin();

        $this->from($this->admin('products/create'))->post($this->admin('products'), ['name' => '', 'category_id' => $tools->id, 'status' => 'draft']);
        $islands = $this->islands($this->get($this->admin('products/create'))->getContent());

        $this->assertTrue($islands[0]['props']['invalid']);
        $this->assertFalse($islands[2]['props']['invalid']);
        $this->assertSame((string) $tools->id, $islands[2]['props']['value'], 'Old input fills the component');
    }

    public function test_bulk_actions_and_confirmations_need_no_inline_script()
    {
        $this->seedProducts();
        $this->loginAsAdmin();

        $html = $this->get($this->admin('products'))->getContent();
        $bulk = array_values(array_filter($this->islands($html), fn ($island) => $island['component'] === 'BulkActions'))[0]['props'];

        $this->assertSame(['deactivate', 'delete'], array_column($bulk['actions'], 'name'));
        $this->assertSame(url($this->admin('products/bulk/delete')), $bulk['actions'][1]['url']);
        $this->assertStringContainsString('data-cb-confirm="Delete this record?"', $html);
        $this->assertStringNotContainsString('onclick="return confirm', $html);
    }

    public function test_classic_modules_keep_the_adminlte_layout()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('users'))->assertOk()
            ->assertSee('adminlte.min.css', false)
            ->assertDontSee('vendor/crudbooster/panel/panel.css', false)
            // The AdminLTE sidebar still lists panel links in its own markup.
            ->assertSee('class="nav-link "', false)
            ->assertDontSee('cb-nav-link', false);
    }

    public function test_page_components_render_in_either_theme()
    {
        $html = $this->blade('<x-cb::stat label="Orders" value="12" icon="fa fa-cube" color="success" url="/orders" /><x-cb::badge color="danger">Late</x-cb::badge>');
        $html->assertSee('href="/orders"', false)->assertSee('Orders')->assertSee(TailwindTheme::badge('danger'), false);

        app(Panel::class)->theme('adminlte');
        $this->blade('<x-cb::stat label="Orders" value="12" color="success" /><x-cb::card title="Totals">Body</x-cb::card>')
            ->assertSee('small-box text-bg-success', false)->assertSee('card-title', false);
    }

    public function test_themes_can_be_switched_and_added_at_runtime()
    {
        $this->seedProducts();
        $this->loginAsAdmin();

        app(Panel::class)->theme('adminlte');
        $this->get($this->admin('products'))->assertSee('adminlte.min.css', false);

        $directory = sys_get_temp_dir().'/cb-theme-'.uniqid();
        mkdir($directory.'/resources', 0777, true);
        file_put_contents($directory.'/resources/index.blade.php', 'Custom list of {{ $resource::getPluralLabel() }}');
        app(Panel::class)->theme(new class($directory) extends Theme
        {
            public function __construct(private string $directory)
            {
            }

            public function getName(): string
            {
                return 'mine';
            }

            public function getViewPaths(): array
            {
                return [$this->directory];
            }
        });

        $this->get($this->admin('products'))->assertOk()->assertSeeText('Custom list of Products');
        // Views the theme does not have come from the default theme.
        $this->get($this->admin('products/create'))->assertOk()->assertSee('id="cb-save"', false);
        $this->assertSame('mine', app(Panel::class)->getTheme()->getName());

        $this->expectException(\InvalidArgumentException::class);
        app(Panel::class)->theme('missing');
    }

    /**
     * @return array<int, array{component: string, props: array}>
     */
    private function islands(string $html): array
    {
        preg_match_all('/data-cb-component="([^"]+)"(?: data-cb-props="([^"]*)")?/', $html, $matches, PREG_SET_ORDER);

        return array_values(array_filter(array_map(fn ($match) => [
            'component' => $match[1],
            'props' => json_decode(html_entity_decode($match[2] ?? '{}', ENT_QUOTES), true) ?? [],
        ], $matches), fn ($island) => $island['component'] !== 'ThemeToggle'));
    }
}
