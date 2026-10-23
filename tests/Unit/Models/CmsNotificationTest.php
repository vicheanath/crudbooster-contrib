<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsNotification;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsNotificationTest extends TestCase
{
    public function test_visible_to_groups_own_and_broadcast_before_other_constraints(): void
    {
        $this->notification('mine unread', 5, 0);
        $this->notification('mine read', 5, 1);
        $this->notification('broadcast unread', CmsNotification::BROADCAST, 0);
        $this->notification('broadcast read', CmsNotification::BROADCAST, 1);
        $this->notification('someone else', 6, 0);

        // Without the grouping, "broadcast read" leaked through `id_cms_users = 0 OR (... AND is_read = 0)`.
        $this->assertSame(
            ['mine unread', 'broadcast unread'],
            CmsNotification::query()->visibleTo(5)->unread()->orderBy('id')->pluck('content')->all()
        );
        $this->assertSame(4, CmsNotification::query()->visibleTo(5)->count());
    }

    private function notification(string $content, int $userId, int $read): void
    {
        CmsNotification::query()->create(['content' => $content, 'id_cms_users' => $userId, 'is_read' => $read, 'url' => '/']);
    }
}
