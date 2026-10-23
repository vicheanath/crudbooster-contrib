<?php

namespace crocodicstudio\crudbooster\contracts\Notifications;

interface NotificationSenderInterface
{
    /**
     * Adds an unread cms_notifications entry linking to `$url` for each user, or for the current
     * admin when `$userIds` is empty.
     */
    public function send(string $content, ?string $url = null, array $userIds = []): void;
}
