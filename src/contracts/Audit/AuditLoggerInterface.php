<?php

namespace crocodicstudio\crudbooster\contracts\Audit;

interface AuditLoggerInterface
{
    public function log(string $description, string $details = ''): void;
}
