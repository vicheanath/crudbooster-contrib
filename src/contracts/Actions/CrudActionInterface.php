<?php

namespace crocodicstudio\crudbooster\contracts\Actions;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * One CBController endpoint. Actions read the module's configuration from the controller
 * and call its hooks, so modules written for 5.x keep working unchanged.
 *
 * @deprecated 7.2 Only used by CBController modules. Resources run row and bulk actions with
 *     CrudBooster\Actions\Action and BulkAction. See docs/en/deprecations.md.
 */
interface CrudActionInterface
{
    public function execute(CBController $controller, mixed ...$arguments): mixed;
}
