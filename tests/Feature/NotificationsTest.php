<?php

namespace crocodicstudio\crudbooster\tests\Feature;

use crocodicstudio\crudbooster\models\CmsNotification;

class NotificationsTest extends FeatureTestCase
{
    public function test_latest_json_lists_unread_own_and_broadcast_notifications_only(): void
    {
        $this->loginAsAdmin();
        $me = session('admin_id');
        foreach ([['mine', $me, 0], ['mine read', $me, 1], ['everyone', 0, 0], ['everyone read', 0, 1], ['other user', 999, 0]] as [$content, $user, $read]) {
            CmsNotification::query()->create(['content' => $content, 'id_cms_users' => $user, 'is_read' => $read, 'url' => '/']);
        }

        $json = $this->get($this->admin('notifications/latest-json'))->assertOk()->json();

        $this->assertSame(2, $json['total']);
        $this->assertEqualsCanonicalizing(['mine', 'everyone'], array_column($json['items'], 'content'));
    }

    public function test_reading_another_users_notification_is_not_found(): void
    {
        $this->loginAsAdmin();
        $other = CmsNotification::query()->create(['content' => 'private', 'id_cms_users' => 999, 'is_read' => 0, 'url' => '/']);

        $this->get($this->admin("notifications/read/{$other->id}"))->assertNotFound();

        $this->assertFalse($other->fresh()->is_read);
    }
}
