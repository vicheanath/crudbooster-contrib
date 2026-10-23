<?php

namespace crocodicstudio\crudbooster\notifications;

use crocodicstudio\crudbooster\contracts\Notifications\NotificationSenderInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\models\CmsNotification;

final class NotificationSender implements NotificationSenderInterface
{
    public function __construct(
        private readonly CmsNotification $notifications,
        private readonly AuthenticationServiceInterface $auth,
    ) {
    }

    public function send(string $content, ?string $url = null, array $userIds = []): void
    {
        foreach ($userIds ?: [$this->auth->currentUserId()] as $userId) {
            $this->notifications->newQuery()->toBase()->insert([
                'created_at' => now()->format('Y-m-d H:i:s'),
                'id_cms_users' => $userId,
                'content' => $content,
                'is_read' => 0,
                'url' => $url,
            ]);
        }
    }
}
