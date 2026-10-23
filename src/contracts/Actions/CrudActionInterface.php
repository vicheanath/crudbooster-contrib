<?php

namespace crocodicstudio\crudbooster\contracts\Actions;

use crocodicstudio\crudbooster\controllers\CBController;

/**
 * One CBController endpoint. Actions read the module's configuration from the controller
 * and call its hooks, so modules written for 5.x keep working unchanged.
 */
interface CrudActionInterface
{
    public function execute(CBController $controller, mixed ...$arguments): mixed;
}
