<?php

namespace crocodicstudio\crudbooster\actions;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Grid\GridQueryBuilderInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\controllers\CBController;
use crocodicstudio\crudbooster\grid\GridRowRenderer;
use CRUDBooster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * The index page. With $index_return set it returns the query result and columns instead
 * (used by export).
 *
 * @deprecated 7.2 Only used by CBController modules; a resource's table() builds its list. See
 *     docs/en/deprecations.md.
 */
class IndexAction extends CrudAction
{
    public function __construct(
        PermissionCheckerInterface $permissions,
        AuditLoggerInterface $audit,
        protected readonly GridQueryBuilderInterface $grid,
        protected readonly GridRowRenderer $renderer,
        protected readonly SchemaInspectorInterface $schema,
    ) {
        parent::__construct($permissions, $audit);
    }

    public function execute(CBController $controller, mixed ...$arguments): mixed
    {
        $module = $this->moduleName();

        $this->authorize($this->permissions->canView() || $controller->global_privilege, cbLang('log_try_view', ['module' => $module]));

        $data = [];

        if (request('parent_table')) {
            $data['parent_table'] = DB::table(request('parent_table'))->where($this->schema->primaryKey(g('parent_table')), request('parent_id'))->first();
            $data['parent_field'] = request('foreign_key') ?: CRUDBooster::getTableForeignKey(g('parent_table'));

            // The parent's column is implied, so it is not listed.
            if ($data['parent_field']) {
                foreach ($controller->columns_table as $i => $column) {
                    if (($column['name'] ?? null) == $data['parent_field']) {
                        unset($controller->columns_table[$i]);
                    }
                }
            }
        }

        $data['table'] = $controller->table;
        $data['table_pk'] = $this->schema->primaryKey($controller->table);
        $data['page_title'] = $module;
        $data['page_description'] = cbLang('default_module_description');
        $data['date_candidate'] = $controller->date_candidate;
        $data['limit'] = $limit = request('limit') ?: $controller->limit;

        $grid = $this->grid->buildQuery($controller->table, $controller->primary_key, $controller->columns_table, [
            'scope' => function (&$query) use ($controller) {
                if (request('parent_id')) {
                    $query->where(CRUDBooster::parseSqlTable($controller->table)['table'].'.'.request('foreign_key'), request('parent_id'));
                }
                $controller->hook_query_index($query);
            },
            'search' => request('q'),
            'where' => request('where'),
            'filters' => request('filter_column'),
            'orderby' => $controller->orderby,
        ]);

        $data['result'] = $grid->query->paginate($limit);
        $data['columns'] = $grid->columns;

        if ($controller->index_return) {
            return $data;
        }

        $html = $this->renderer->render($controller, $data['result'], $grid->columns, $this->rowActions($controller), (int) $limit);
        $data['html_contents'] = ['html' => $html, 'data' => $data['result']];

        return view('crudbooster::default.index', $data);
    }

    /**
     * The module's row buttons, plus one per sub module.
     */
    protected function rowActions(CBController $controller): ?array
    {
        $addaction = $controller->data['addaction'];

        foreach ($controller->sub_module ?: [] as $sub) {
            $parentTable = CRUDBooster::parseSqlTable($controller->table)['table'];
            $addaction[] = [
                'label' => $sub['label'],
                'icon' => $sub['button_icon'] ?? null,
                'url' => CRUDBooster::adminPath($sub['path']).'?return_url='.urlencode(Request::fullUrl()).'&parent_table='.$parentTable.'&parent_columns='.($sub['parent_columns'] ?? '').'&parent_columns_alias='.($sub['parent_columns_alias'] ?? '').'&parent_id=['.($sub['custom_parent_id'] ?? 'id').']&foreign_key='.($sub['foreign_key'] ?? '').'&label='.urlencode($sub['label']),
                'color' => $sub['button_color'] ?? null,
                'showIf' => $sub['showIf'] ?? null,
            ];
        }

        return $addaction;
    }
}
