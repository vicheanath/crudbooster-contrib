<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ActionsTest extends FeatureTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);
        $app['config']->set('mail.default', 'array');
    }

    private function template(array $overrides = [])
    {
        return $overrides + [
            'name' => 'Order Shipped',
            'slug' => 'order_shipped',
            'subject' => 'Your order [id] shipped',
            'content' => '<p>Hello [name]</p>',
            'description' => 'Sent when an order ships',
            'from_name' => 'Shop',
            'from_email' => 'shop@example.com',
            'cc_email' => '',
        ];
    }

    public function test_crud_add_edit_detail_delete()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('email_templates/add'))->assertOk()
            ->assertSee('name="subject"', false)
            ->assertDontSee('NO COMPONENT', false);

        $this->post($this->admin('email_templates/add-save'), $this->template())->assertRedirect();
        $id = DB::table('cms_email_templates')->where('slug', 'order_shipped')->value('id');
        $this->assertNotNull($id, 'Add did not insert the row');

        $this->post($this->admin("email_templates/edit-save/$id"), $this->template(['subject' => 'Shipped!', 'slug' => 'order_shipped_v2']))->assertRedirect();
        $this->assertDatabaseHas('cms_email_templates', ['id' => $id, 'subject' => 'Shipped!']);

        $this->get($this->admin("email_templates/detail/$id"))->assertOk()->assertSee('Shipped!');

        $this->get($this->admin("email_templates/delete/$id"))->assertRedirect();
        $this->assertDatabaseMissing('cms_email_templates', ['id' => $id]);
    }

    public function test_validation_errors_redirect_back_with_message()
    {
        $this->loginAsAdmin();

        $this->from($this->admin('email_templates/add'))
            ->post($this->admin('email_templates/add-save'), $this->template(['subject' => 'ab']))
            ->assertRedirect($this->admin('email_templates/add'))
            ->assertSessionHas('message_type', 'warning');

        $this->assertDatabaseMissing('cms_email_templates', ['slug' => 'order_shipped']);
    }

    public function test_hidden_required_field_filled_by_hook_is_saved()
    {
        require_once __DIR__.'/../fixtures/AdminHookFilledController.php';
        DB::table('cms_moduls')->insert(['name' => 'Hook Filled', 'path' => 'hook_filled', 'table_name' => 'cms_email_templates', 'controller' => 'AdminHookFilledController', 'is_protected' => 0, 'is_active' => 1]);
        $this->registerCrudboosterRoutes();
        $this->loginAsAdmin();

        // The hidden field is submitted empty, exactly as the browser sends it.
        $this->post($this->admin('hook_filled/add-save'), ['description' => '', 'name' => 'Hooked', 'slug' => 'hooked'])
            ->assertRedirect()
            ->assertSessionHas('message_type', 'success');

        $this->assertDatabaseHas('cms_email_templates', ['slug' => 'hooked', 'description' => 'filled by hook']);
    }

    public function test_bulk_delete_selected()
    {
        $this->loginAsAdmin();
        $ids = [
            DB::table('cms_email_templates')->insertGetId($this->template(['slug' => 'a'])),
            DB::table('cms_email_templates')->insertGetId($this->template(['slug' => 'b'])),
        ];

        $this->from($this->admin('email_templates'))
            ->post($this->admin('email_templates/action-selected'), ['checkbox' => $ids, 'button_name' => 'delete'])
            ->assertRedirect();

        $this->assertSame(0, DB::table('cms_email_templates')->whereIn('id', $ids)->count());
    }

    public function test_privilege_with_module_permissions_is_saved()
    {
        $this->loginAsAdmin();
        $module = DB::table('cms_moduls')->where('path', 'email_templates')->value('id');

        $this->post($this->admin('privileges/add-save'), [
            'name' => 'Editor',
            'is_superadmin' => 0,
            'theme_color' => 'skin-blue',
            'privileges' => [$module => ['is_visible' => 1, 'is_create' => 1, 'is_read' => 1, 'is_edit' => 1, 'is_delete' => 0]],
        ])->assertRedirect();

        $privilege = DB::table('cms_privileges')->where('name', 'Editor')->value('id');
        $this->assertNotNull($privilege);
        $this->assertDatabaseHas('cms_privileges_roles', ['id_cms_privileges' => $privilege, 'id_cms_moduls' => $module, 'is_create' => 1, 'is_delete' => 0]);
    }

    public function test_non_superadmin_is_denied_module_without_permission()
    {
        $privilege = DB::table('cms_privileges')->insertGetId(['name' => 'Viewer', 'is_superadmin' => 0, 'theme_color' => 'skin-blue']);
        DB::table('cms_users')->insert(['name' => 'Viewer', 'email' => 'viewer@example.com', 'password' => Hash::make('secret'), 'id_cms_privileges' => $privilege, 'status' => 'Active']);

        $this->post($this->admin('login'), ['email' => 'viewer@example.com', 'password' => 'secret'])->assertRedirect();

        $this->get($this->admin('email_templates'))->assertRedirect();
        $this->assertSame(cbLang('denied_access'), session('message'));
    }

    public function test_read_only_role_cannot_open_edit_form()
    {
        $privilege = DB::table('cms_privileges')->insertGetId(['name' => 'Reader', 'is_superadmin' => 0, 'theme_color' => 'skin-blue']);
        $module = DB::table('cms_moduls')->where('path', 'email_templates')->value('id');
        DB::table('cms_privileges_roles')->insert(['id_cms_privileges' => $privilege, 'id_cms_moduls' => $module, 'is_visible' => 1, 'is_read' => 1, 'is_create' => 0, 'is_edit' => 0, 'is_delete' => 0]);
        DB::table('cms_users')->insert(['name' => 'Reader', 'email' => 'reader@example.com', 'password' => Hash::make('secret'), 'id_cms_privileges' => $privilege, 'status' => 'Active']);
        $id = DB::table('cms_email_templates')->insertGetId($this->template(['slug' => 'readonly']));

        $this->post($this->admin('login'), ['email' => 'reader@example.com', 'password' => 'secret'])->assertRedirect();

        $this->get($this->admin("email_templates/detail/$id"))->assertOk();
        $this->get($this->admin("email_templates/edit/$id"))->assertRedirect();
        $this->assertSame(cbLang('denied_access'), session('message'));
    }

    public function test_settings_are_saved_and_cached_values_refreshed()
    {
        $this->loginAsAdmin();
        $this->assertSame('CRUDBooster', CRUDBooster::getSetting('appname'));

        $this->post($this->admin('settings/save-setting?group_setting='.urlencode(cbLang('application_setting'))), ['appname' => 'My Admin'])
            ->assertRedirect();

        $this->assertSame('My Admin', DB::table('cms_settings')->where('name', 'appname')->value('content'));
        $this->assertSame('My Admin', CRUDBooster::getSetting('appname'));
    }

    public function test_lock_and_unlock_screen()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('lock-screen'))->assertOk();
        $this->get($this->admin('users'))->assertRedirect($this->admin('lock-screen'));

        $this->post($this->admin('unlock-screen'), ['password' => '123456'])->assertRedirect();
        $this->get($this->admin('users'))->assertOk();
    }

    public function test_notifications_latest_json_and_read()
    {
        $this->loginAsAdmin();
        $id = DB::table('cms_notifications')->insertGetId(['id_cms_users' => session('admin_id'), 'content' => 'Hi', 'url' => 'https://example.com', 'is_read' => 0, 'created_at' => now()]);

        $this->get($this->admin('notifications/latest-json'))->assertOk()->assertJsonFragment(['total' => 1]);
        $this->get($this->admin("notifications/read/$id"))->assertRedirect('https://example.com');
        $this->assertDatabaseHas('cms_notifications', ['id' => $id, 'is_read' => 1]);
    }

    public function test_forgot_password_emails_a_new_password()
    {
        $this->post($this->admin('forgot'), ['email' => 'admin@crudbooster.com'])->assertRedirect();

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertSame('admin@crudbooster.com', $messages[0]->getOriginalMessage()->getTo()[0]->getAddress());
        $this->assertFalse(Hash::check('123456', DB::table('cms_users')->where('email', 'admin@crudbooster.com')->value('password')));
    }

    public function test_send_email_now_and_through_the_queue_command()
    {
        DB::table('cms_email_templates')->insert($this->template());

        CRUDBooster::sendEmail(['to' => 'buyer@example.com', 'data' => ['id' => 42, 'name' => 'Ann'], 'template' => 'order_shipped']);
        CRUDBooster::sendEmail(['to' => 'later@example.com', 'data' => ['id' => 43, 'name' => 'Bob'], 'template' => 'order_shipped', 'send_at' => now()->subMinute()->format('Y-m-d H:i:s')]);

        $this->assertSame(1, DB::table('cms_email_queues')->count());
        $this->artisan('mailqueues')->assertSuccessful();
        $this->assertSame(0, DB::table('cms_email_queues')->count());

        $messages = collect(app('mailer')->getSymfonyTransport()->messages())->map->getOriginalMessage();
        $this->assertSame(['Your order 42 shipped', 'Your order 43 shipped'], $messages->map->getSubject()->all());
        $this->assertStringContainsString('Hello Ann', $messages[0]->getHtmlBody());
    }

    public function test_smtp_settings_configure_a_dedicated_mailer()
    {
        foreach (['smtp_driver' => 'smtp', 'smtp_host' => 'smtp.example.com', 'smtp_port' => '2525', 'smtp_username' => 'u', 'smtp_password' => 'p'] as $name => $value) {
            DB::table('cms_settings')->where('name', $name)->update(['content' => $value]);
        }
        Cache::flush();

        $this->assertSame('cb_smtp', CRUDBooster::mailer());
        $this->assertSame('smtp.example.com', config('mail.mailers.cb_smtp.host'));
        $this->assertSame('2525', config('mail.mailers.cb_smtp.port'));

        DB::table('cms_settings')->where('name', 'smtp_driver')->update(['content' => 'mail']);
        Cache::flush();
        $this->assertNull(CRUDBooster::mailer());
    }
}
