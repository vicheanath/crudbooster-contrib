<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\export\ExporterRegistry;
use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Request;

/**
 * Exports the index page's rows (filters included) in the requested format.
 */
class ExportAction extends CrudAction
{
    public function __construct(
        PermissionCheckerInterface $permissions,
        AuditLoggerInterface $audit,
        protected readonly ExporterRegistry $exporters,
    ) {
        parent::__construct($permissions, $audit);
    }

    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(180);

        $controller->limit = Request::input('limit');
        $controller->index_return = true;
        // getIndex() may be overridden by the module.
        $data = $controller->getIndex();

        if (Request::input('default_paper_size')) {
            // Resolved here rather than injected, so subclasses keep their constructor signature.
            app(SettingsRepositoryInterface::class)->set('default_paper_size', Request::input('page_size'));
        }

        $exporter = $this->exporters->get((string) Request::input('fileformat'));

        return $exporter?->export($data, [
            'filename' => Request::input('filename'),
            'page_size' => Request::input('page_size'),
            'page_orientation' => Request::input('page_orientation'),
        ]);
    }
}
