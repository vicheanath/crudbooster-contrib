<?php

namespace crocodicstudio\crudbooster\tests\Unit\Models;

use crocodicstudio\crudbooster\models\CmsEmailQueue;
use crocodicstudio\crudbooster\tests\TestCase;

class CmsEmailQueueTest extends TestCase
{
    public function test_due_returns_emails_whose_send_time_has_passed(): void
    {
        CmsEmailQueue::query()->create(['email_subject' => 'past', 'send_at' => '2026-01-01 10:00:00']);
        CmsEmailQueue::query()->create(['email_subject' => 'now', 'send_at' => '2026-01-01 12:00:00']);
        CmsEmailQueue::query()->create(['email_subject' => 'future', 'send_at' => '2026-01-01 12:00:01']);

        $this->assertSame(['past', 'now'], CmsEmailQueue::query()->due('2026-01-01 12:00:00')->orderBy('id')->pluck('email_subject')->all());
        $this->assertSame(3, CmsEmailQueue::query()->due()->count());
    }
}
