<?php

namespace crocodicstudio\crudbooster\services;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\models\CmsLog;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

final class AuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private readonly CmsLog $logs,
        private readonly Session $session,
        private readonly Request $request,
    ) {
    }

    public function log(string $description, string $details = ''): void
    {
        $this->logs->newQuery()->create([
            'ipaddress' => $this->request->ip(),
            'useragent' => $this->request->userAgent(),
            'url' => $this->request->fullUrl(),
            'description' => $description,
            'details' => $details,
            'id_cms_users' => $this->session->get('admin_id'),
        ]);
    }
}
