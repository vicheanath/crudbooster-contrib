<?php

namespace CrudBooster\Resources;

use CrudBooster\Tables\Columns\Column;

/**
 * The entries of a resource's detail page. They are table columns, so formatting is shared:
 *
 *     return $detail->entries([TextColumn::make('name'), TextColumn::make('price')->money('$')]);
 */
class Detail
{
    /** @var Column[] */
    protected array $entries = [];

    public function entries(array $columns): static
    {
        $this->entries = $columns;

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getEntries(): array
    {
        return array_values(array_filter($this->entries, fn (Column $column) => $column->isVisible()));
    }

    public function getEagerLoads(): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (Column $column) => $column->getRelationshipName(), $this->getEntries()
        ))));
    }
}
