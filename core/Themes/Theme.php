<?php

namespace CrudBooster\Themes;

use Illuminate\Support\HtmlString;

/**
 * The look of the panel's resource and page screens. A theme provides Blade views that take
 * precedence over the default ones (any view it does not have falls back to the default), and
 * the HTML its layout adds to <head>, such as stylesheets and scripts.
 *
 * Classic CBController modules always use the AdminLTE layout, whatever the panel's theme.
 */
abstract class Theme
{
    abstract public function getName(): string;

    /**
     * Directories searched for cb:: views before the default ones, most specific first.
     *
     * @return string[]
     */
    public function getViewPaths(): array
    {
        return [];
    }

    /**
     * Stylesheets and scripts for the theme's layout.
     */
    public function getHeadHtml(): HtmlString
    {
        return new HtmlString('');
    }
}
