<?php

namespace crocodicstudio\crudbooster\audit;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use crocodicstudio\crudbooster\models\CmsLog;
use Illuminate\Http\Request;

/**
 * Writes cms_logs entries. As in 5.x, nothing is logged unless the "api_debug_mode" setting is on.
 */
final class AuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private readonly CmsLog $logs,
        private readonly AuthenticationServiceInterface $auth,
        private readonly Request $request,
        private readonly SettingsRepositoryInterface $settings,
    ) {
    }

    public function log(string $description, string $details = ''): void
    {
        if (! $this->settings->get('api_debug_mode')) {
            return;
        }

        $this->logs->newQuery()->create([
            'created_at' => now()->format('Y-m-d H:i:s'),
            'ipaddress' => $this->request->ip(),
            'useragent' => $this->request->userAgent(),
            'url' => $this->request->url(),
            'description' => $description,
            'details' => $details,
            'id_cms_users' => $this->auth->currentUserId(),
        ]);
    }
}
