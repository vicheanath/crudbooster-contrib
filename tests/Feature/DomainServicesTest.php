<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\contracts\Generator\ApiGeneratorInterface;
use crocodicstudio\crudbooster\contracts\Generator\ModuleGeneratorInterface;
use crocodicstudio\crudbooster\contracts\Mail\TemplateMailerInterface;
use crocodicstudio\crudbooster\contracts\Navigation\MenuRepositoryInterface;
use crocodicstudio\crudbooster\contracts\Notifications\NotificationSenderInterface;
use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use crocodicstudio\crudbooster\tests\TestCase;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

/**
 * Settings, navigation, mail, notifications and the code generators: the services behind the
 * remaining CRUDBooster static helpers.
 */
class DomainServicesTest extends TestCase
{
    public function test_helpers_delegate_to_replaceable_services(): void
    {
        $this->app->instance(SettingsRepositoryInterface::class, new class implements SettingsRepositoryInterface {
            public function get(string $name): mixed
            {
                return 'from-'.$name;
            }

            public function forget(string $name): void
            {
            }
        });

        $this->assertSame('from-appname', CRUDBooster::getSetting('appname'));

        foreach ([ModuleGeneratorInterface::class, ApiGeneratorInterface::class, TemplateMailerInterface::class, MenuRepositoryInterface::class, NotificationSenderInterface::class] as $contract) {
            $this->assertInstanceOf($contract, app($contract));
        }
    }

    public function test_settings_are_cached_until_forgotten(): void
    {
        $settings = app(SettingsRepositoryInterface::class);
        DB::table('cms_settings')->where('name', 'appname')->update(['content' => 'First']);

        $this->assertSame('First', $settings->get('appname'));

        DB::table('cms_settings')->where('name', 'appname')->update(['content' => 'Second']);
        $this->assertSame('First', $settings->get('appname'));

        $settings->forget('appname');
        $this->assertSame('Second', $settings->get('appname'));
        $this->assertNull($settings->get('no_such_setting'));
    }

    public function test_sidebar_lists_the_privileges_menus_with_children_and_broken_links(): void
    {
        $privilege = 7;
        $parent = $this->menu(['name' => 'Shop', 'type' => 'URL', 'path' => 'https://example.com/shop', 'sorting' => 1], $privilege);
        $this->menu(['name' => 'Orders', 'type' => 'Module', 'path' => 'orders', 'parent_id' => $parent, 'sorting' => 1], $privilege);
        $this->menu(['name' => 'Broken', 'type' => 'Route', 'path' => 'no.such.route', 'sorting' => 2], $privilege);
        $this->menu(['name' => 'Inactive', 'type' => 'URL', 'path' => '/off', 'is_active' => 0], $privilege);
        $this->menu(['name' => 'Other privilege', 'type' => 'URL', 'path' => '/other'], $privilege + 1);
        $this->menu(['name' => 'Home', 'type' => 'Module', 'path' => 'home', 'is_dashboard' => 1, 'color' => 'red'], $privilege);
        Session::put('admin_privileges', $privilege);

        $menus = CRUDBooster::sidebarMenu();

        $this->assertSame(['Shop', 'Broken'], $menus->pluck('name')->all());
        $this->assertSame('https://example.com/shop', $menus[0]->url);
        $this->assertSame(url(config('crudbooster.ADMIN_PATH').'/orders'), $menus[0]->children[0]->url);
        $this->assertSame(config('crudbooster.ADMIN_PATH').'/orders', $menus[0]->children[0]->url_path);
        $this->assertSame('#', $menus[1]->url);
        $this->assertTrue($menus[1]->is_broken);

        $dashboard = CRUDBooster::sidebarDashboard();
        $this->assertSame('Home', $dashboard->name);
        $this->assertSame(url(config('crudbooster.ADMIN_PATH').'/home'), $dashboard->url);

        Session::put('admin_privileges', 99);
        $this->assertNull(CRUDBooster::sidebarDashboard()->id);
        $this->assertCount(0, CRUDBooster::sidebarMenu());
    }

    public function test_notifications_go_to_the_given_users_or_the_current_admin(): void
    {
        Session::put('admin_id', 5);

        CRUDBooster::sendNotification(['content' => 'Mine', 'to' => '/admin/orders']);
        app(NotificationSenderInterface::class)->send('Theirs', null, [2, 3]);

        $this->assertSame([5], DB::table('cms_notifications')->where('content', 'Mine')->pluck('id_cms_users')->map('intval')->all());
        $this->assertSame([2, 3], DB::table('cms_notifications')->where('content', 'Theirs')->pluck('id_cms_users')->map('intval')->all());
        $this->assertDatabaseHas('cms_notifications', ['content' => 'Mine', 'url' => '/admin/orders', 'is_read' => 0]);
    }

    public function test_sending_an_unknown_email_template_fails_clearly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email template [missing] does not exist.');

        CRUDBooster::sendEmail(['to' => 'a@example.com', 'data' => [], 'template' => 'missing']);
    }

    public function test_queued_attachments_never_revive_objects(): void
    {
        $queue = (object) [
            'email_content' => '<p>Hi</p>',
            'email_recipient' => 'a@example.com',
            'email_subject' => 'Hi',
            'email_from_email' => null,
            'email_from_name' => null,
            'email_cc_email' => null,
            'email_attachments' => serialize([new WakeupProbe()]),
        ];

        try {
            app(TemplateMailerInterface::class)->sendQueued($queue);
        } catch (\Throwable $e) {
            // An incomplete class is not a valid attachment; only the revival matters here.
        }

        $this->assertFalse(WakeupProbe::$woken);
    }

    private function menu(array $attributes, int $privilegeId): int
    {
        $id = DB::table('cms_menus')->insertGetId($attributes + ['parent_id' => 0, 'is_active' => 1, 'is_dashboard' => 0]);
        DB::table('cms_menus_privileges')->insert(['id_cms_menus' => $id, 'id_cms_privileges' => $privilegeId]);

        return $id;
    }
}

class WakeupProbe
{
    public static bool $woken = false;

    public function __wakeup(): void
    {
        self::$woken = true;
    }
}
