<?php

namespace CrudBooster\Http\Controllers;

use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Form;
use CrudBooster\Resources\Detail;
use CrudBooster\Resources\Resource;
use CrudBooster\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves every resource's pages; the route says which resource ("resource" default).
 */
class ResourceController extends Controller
{
    public function __construct(
        private readonly AuditLoggerInterface $audit,
    ) {
    }

    public function index(Request $request)
    {
        $resource = $this->resource($request);
        $this->authorize($resource, 'viewAny');

        $table = $resource->table(new Table);
        $query = $table->applyToQuery(
            $resource->query(),
            $request->query('search'),
            (array) $request->query('filters', []),
            $request->query('sort'),
            $request->query('direction'),
        );

        $perPage = (int) $request->query('per_page');
        if (! in_array($perPage, $table->getPerPageOptions(), true)) {
            $perPage = $table->getPerPage();
        }

        return view('cb::resources.index', [
            'resource' => $resource,
            'table' => $table,
            'model' => $resource::newModel(),
            'records' => $query->paginate($perPage)->withQueryString(),
            'search' => (string) $request->query('search', ''),
            'filterValues' => (array) $request->query('filters', []),
            'sort' => $request->query('sort'),
            'direction' => $request->query('direction') === 'desc' ? 'desc' : 'asc',
            'page_title' => $resource::getPluralLabel(),
        ]);
    }

    public function create(Request $request)
    {
        $resource = $this->resource($request);
        $this->authorize($resource, 'create');

        return $this->form($resource, null);
    }

    public function store(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $this->authorize($resource, 'create');

        $form = $this->bindForm($resource, null);
        $request->validate($form->getValidationRules(), [], $form->getValidationAttributes());

        $record = DB::transaction(function () use ($resource, $form, $request) {
            [$attributes, $relationships] = $form->getSubmittedState($request);
            $record = $resource::newModel();
            $record->forceFill($resource->beforeCreate($attributes))->save();
            $form->saveRelationships($record, $relationships);
            $resource->afterCreate($record);

            return $record;
        });

        $title = $resource->getRecordTitle($record);
        $this->audit->log(__('crudbooster::crudbooster.log_add', ['name' => $title, 'module' => $resource::getPluralLabel()]));

        $redirect = $request->input('_after') === 'create' && $resource->can('create')
            ? $resource::url('create')
            : $this->returnUrl($request, $resource);

        return redirect($redirect)->with($this->message(__(':label ":title" was created.', ['label' => $resource::getLabel(), 'title' => $title])));
    }

    public function show(Request $request)
    {
        $resource = $this->resource($request);
        $record = $this->record($request, $resource);
        $this->authorize($resource, 'view', $record);

        $detail = $resource->detail(new Detail);
        $record->loadMissing($detail->getEagerLoads());

        return view('cb::resources.show', [
            'resource' => $resource,
            'record' => $record,
            'detail' => $detail,
            'page_title' => $resource->getRecordTitle($record),
        ]);
    }

    public function edit(Request $request)
    {
        $resource = $this->resource($request);
        $record = $this->record($request, $resource);
        $this->authorize($resource, 'update', $record);

        return $this->form($resource, $record);
    }

    public function update(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $record = $this->record($request, $resource);
        $this->authorize($resource, 'update', $record);

        $form = $this->bindForm($resource, $record);
        $request->validate($form->getValidationRules(), [], $form->getValidationAttributes());

        DB::transaction(function () use ($resource, $form, $request, $record) {
            [$attributes, $relationships] = $form->getSubmittedState($request);
            $record->forceFill($resource->beforeUpdate($attributes, $record))->save();
            $form->saveRelationships($record, $relationships);
            $resource->afterUpdate($record);
        });

        $title = $resource->getRecordTitle($record);
        $this->audit->log(__('crudbooster::crudbooster.log_update', ['name' => $title, 'module' => $resource::getPluralLabel()]));

        return redirect($this->returnUrl($request, $resource))
            ->with($this->message(__(':label ":title" was saved.', ['label' => $resource::getLabel(), 'title' => $title])));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $resource = $this->resource($request);
        $record = $this->record($request, $resource);
        $this->authorize($resource, 'delete', $record);

        $this->delete($resource, $record);

        return redirect($this->returnUrl($request, $resource))
            ->with($this->message(__(':label ":title" was deleted.', ['label' => $resource::getLabel(), 'title' => $resource->getRecordTitle($record)])));
    }

    public function action(Request $request): Response
    {
        $resource = $this->resource($request);
        $record = $this->record($request, $resource);
        $this->authorize($resource, 'view', $record);

        $action = $resource->table(new Table)->getAction((string) $request->route('action'));
        abort_if(! $action || $action->isLink(), 404);

        return $this->run($request, $resource, $action->for(['record' => $record]), $record);
    }

    public function bulk(Request $request): Response
    {
        $resource = $this->resource($request);
        $this->authorize($resource, 'viewAny');

        $table = $resource->table(new Table);
        $records = $resource->query()->whereKey((array) $request->input('records', []))->get();
        $name = (string) $request->route('action');

        if ($name === 'delete' && $table->hasBulkDelete() && ! $table->getBulkAction('delete')) {
            $records = $records->filter(fn (Model $record) => $resource->can('delete', $record));
            abort_if($records->isEmpty() && $request->input('records'), 403);
            foreach ($records as $record) {
                $this->delete($resource, $record);
            }

            return back()->with($this->message(trans_choice(':count record was deleted.|:count records were deleted.', $records->count())));
        }

        $action = $table->getBulkAction($name);
        abort_if(! $action, 404);

        return $this->run($request, $resource, $action->for(['records' => $records]));
    }

    private function run(Request $request, Resource $resource, Action $action, ?Model $record = null): Response
    {
        abort_unless($action->isVisible(), 403);
        if ($ability = $action->getAbility()) {
            $this->authorize($resource, $ability, $record);
        }

        $result = $action->run($request->except(['_token', 'records']));

        if ($result instanceof Response) {
            return $result;
        }

        $message = $action->getSuccessMessage();

        return back()->with($message ? $this->message($message) : []);
    }

    private function delete(Resource $resource, Model $record): void
    {
        DB::transaction(function () use ($resource, $record) {
            $resource->beforeDelete($record);
            $record->delete();
            $resource->afterDelete($record);
        });

        $this->audit->log(__('crudbooster::crudbooster.log_delete', ['name' => $resource->getRecordTitle($record), 'module' => $resource::getPluralLabel()]));
    }

    private function form(Resource $resource, ?Model $record)
    {
        $form = $this->bindForm($resource, $record);
        $operation = $form->getOperation();

        return view('cb::resources.form', [
            'resource' => $resource,
            'form' => $form,
            'record' => $record,
            'operation' => $operation,
            'returnUrl' => request()->query('return_url'),
            'page_title' => $record
                ? __('Edit :title', ['title' => $resource->getRecordTitle($record)])
                : __('New :label', ['label' => $resource::getLabel()]),
        ]);
    }

    private function bindForm(Resource $resource, ?Model $record): Form
    {
        return $resource->form(new Form)->bind($resource::newModel(), $record, $record ? 'edit' : 'create');
    }

    private function resource(Request $request): Resource
    {
        return app($request->route('resource'));
    }

    private function record(Request $request, Resource $resource): Model
    {
        return $resource->resolveRecord($request->route('record'));
    }

    private function authorize(Resource $resource, string $ability, ?Model $record = null): void
    {
        abort_unless($resource->can($ability, $record), 403);
    }

    /**
     * Back to the list the admin came from, when it is one of this resource's list URLs.
     */
    private function returnUrl(Request $request, Resource $resource): string
    {
        $index = $resource::getIndexUrl();
        $returnUrl = (string) $request->input('_return');

        return $resource::hasPage('index') && str_starts_with($returnUrl, $index) ? $returnUrl : $index;
    }

    private function message(string $message, string $type = 'success'): array
    {
        return ['message' => e($message), 'message_type' => $type];
    }
}
