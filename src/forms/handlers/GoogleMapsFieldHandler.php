<?php

namespace crocodicstudio\crudbooster\forms\handlers;

use crocodicstudio\crudbooster\forms\FormContext;

/**
 * Saves the address, and the picked position into the 'latitude' and 'longitude' columns.
 *
 * @deprecated 7.2 Use a ViewField with your own map view, or a custom Field subclass. See
 *     docs/en/deprecations.md.
 */
class GoogleMapsFieldHandler extends BaseFieldHandler
{
    protected array $types = ['googlemaps'];

    protected function transform(array $field, mixed $value, array &$payload, FormContext $context): void
    {
        if (! empty($field['latitude']) && ! empty($field['longitude'])) {
            $payload[$field['latitude']] = request('input-latitude-'.$field['name']);
            $payload[$field['longitude']] = request('input-longitude-'.$field['name']);
        }
    }
}
