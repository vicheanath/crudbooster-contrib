@php
    // Shown the way the form formats it (see asset.blade.php): decimals, separators, prefix and suffix.
    $moneyFormat = (array) ($form['priceformat_parameters'] ?? []);
    $moneyDecimals = (int) ($moneyFormat['centsLimit'] ?? ($form['decimals'] ?? 0));
@endphp
@if($value !== null && $value !== '')
{{ ($moneyFormat['prefix'] ?? '').number_format((float) $value, $moneyDecimals, $moneyFormat['centsSeparator'] ?? '.', $moneyFormat['thousandsSeparator'] ?? ($form['dec_point'] ?? ',')).($moneyFormat['suffix'] ?? '') }}
@endif
