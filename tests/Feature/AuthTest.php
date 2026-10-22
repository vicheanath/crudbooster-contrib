<?php

namespace crocodicstudio\crudbooster\tests\Feature;

class AuthTest extends FeatureTestCase
{
    public function test_admin_can_log_in_and_out()
    {
        $this->loginAsAdmin();
        $this->assertDatabaseHas('cms_logs', ['id_cms_users' => session('admin_id')]);

        $this->get($this->admin('logout'))->assertRedirect();
        $this->assertEmpty(session('admin_id'));
    }

    public function test_wrong_password_is_rejected()
    {
        $this->post($this->admin('login'), ['email' => 'admin@crudbooster.com', 'password' => 'nope'])
            ->assertRedirect();

        $this->assertEmpty(session('admin_id'));
    }

    public function test_forgot_password_page_renders()
    {
        $this->get($this->admin('forgot'))->assertOk();
    }

    public function test_dashboard_renders_for_admin()
    {
        $this->loginAsAdmin();

        $this->get($this->admin('users'))->assertOk()->assertSee('admin@crudbooster.com');
    }
}
