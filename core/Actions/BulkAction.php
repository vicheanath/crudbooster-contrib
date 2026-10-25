<?php

namespace CrudBooster\Actions;

/**
 * Runs on the rows ticked in the table. The action closure receives $records, a collection:
 *
 *     BulkAction::make('archive')->action(fn (Collection $records) => $records->each->archive())
 */
class BulkAction extends Action
{
}
