<?php

namespace CrudBooster\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Decides what the current admin may do in a resource or page. Bind your own implementation to
 * replace the default (policies, then CRUDBooster privileges), e.g. to use spatie/permission.
 */
interface Authorizer
{
    /**
     * @param  string  $ability  viewAny, view, create, update, delete, or a custom ability
     * @param  string  $slug  the resource's or page's slug, the module path in cms_moduls
     * @param  Model|string  $subject  the record, or the model or page class
     */
    public function allows(string $ability, string $slug, Model|string $subject): bool;
}
