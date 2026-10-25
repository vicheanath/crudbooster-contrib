<?php

namespace CrudBooster\Themes;

use Illuminate\Support\HtmlString;

/**
 * Resource pages in a Tailwind CSS layout styled as shadcn/ui (base-nova), with React components
 * on Base UI for the form inputs. The compiled assets ship in src/assets/panel and are published
 * with the "cb_asset" tag.
 *
 * The class lists below are written out in full so Tailwind finds them when it scans this file.
 */
class TailwindTheme extends Theme
{
    public const ASSET_DIRECTORY = 'vendor/crudbooster/panel';

    // shadcn has tokens for primary, secondary and destructive; success, warning and info are
    // tinted the way its destructive badge is.
    private const BADGES = [
        'primary' => 'bg-primary text-primary-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground',
        'success' => 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
        'danger' => 'bg-destructive/10 text-destructive dark:bg-destructive/20',
        'warning' => 'bg-amber-500/15 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
        'info' => 'bg-sky-500/10 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400',
    ];

    private const BUTTONS = [
        'primary' => 'cb-btn-primary',
        'secondary' => '',
        'success' => 'border-transparent bg-emerald-600 text-white hover:bg-emerald-600/85 hover:text-white dark:border-transparent dark:bg-emerald-600 dark:hover:bg-emerald-600/85',
        'danger' => 'cb-btn-destructive',
        'warning' => 'border-transparent bg-amber-500 text-white hover:bg-amber-500/85 hover:text-white dark:border-transparent dark:bg-amber-500 dark:hover:bg-amber-500/85',
        'info' => 'border-transparent bg-sky-600 text-white hover:bg-sky-600/85 hover:text-white dark:border-transparent dark:bg-sky-600 dark:hover:bg-sky-600/85',
    ];

    private const TEXT = [
        'primary' => 'text-foreground',
        'secondary' => 'text-muted-foreground',
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'danger' => 'text-destructive',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'info' => 'text-sky-600 dark:text-sky-400',
    ];

    private const ICON_BACKGROUNDS = [
        'primary' => 'bg-primary text-primary-foreground',
        'secondary' => 'bg-muted text-muted-foreground',
        'success' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        'danger' => 'bg-destructive/10 text-destructive',
        'warning' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400',
        'info' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    ];

    private const SPANS = [
        1 => 'md:col-span-1', 2 => 'md:col-span-2', 3 => 'md:col-span-3', 4 => 'md:col-span-4',
        5 => 'md:col-span-5', 6 => 'md:col-span-6', 7 => 'md:col-span-7', 8 => 'md:col-span-8',
        9 => 'md:col-span-9', 10 => 'md:col-span-10', 11 => 'md:col-span-11', 12 => 'md:col-span-12',
    ];

    private static array $versions = [];

    public function getName(): string
    {
        return 'tailwind';
    }

    public function getViewPaths(): array
    {
        return [dirname(__DIR__).'/resources/views/themes/tailwind'];
    }

    public function getHeadHtml(): HtmlString
    {
        $css = e(asset(self::ASSET_DIRECTORY.'/panel.css').'?v='.$this->version('panel.css'));
        $js = e(asset(self::ASSET_DIRECTORY.'/panel.js').'?v='.$this->version('panel.js'));

        return new HtmlString("<link rel=\"stylesheet\" href=\"$css\">\n<script type=\"module\" src=\"$js\"></script>");
    }

    /**
     * Whether the published copies of the assets are missing or older than the package's.
     */
    public function assetsNeedPublishing(): bool
    {
        foreach (['panel.css', 'panel.js'] as $file) {
            $published = public_path(self::ASSET_DIRECTORY.'/'.$file);
            if (! is_file($published) || md5_file($published) !== md5_file(self::sourcePath($file))) {
                return true;
            }
        }

        return false;
    }

    public static function sourcePath(string $file): string
    {
        return dirname(__DIR__, 2).'/src/assets/panel/'.$file;
    }

    /**
     * Colour classes for a badge (used with .cb-badge): primary, secondary, success, danger,
     * warning or info.
     */
    public static function badge(?string $color): string
    {
        return self::BADGES[$color] ?? self::BADGES['secondary'];
    }

    /**
     * Colour classes for a .cb-btn; secondary is the outline button.
     */
    public static function button(?string $color): string
    {
        return self::BUTTONS[$color] ?? self::BUTTONS['secondary'];
    }

    public static function text(?string $color): string
    {
        return self::TEXT[$color] ?? self::TEXT['secondary'];
    }

    /**
     * Background and icon colour of a stat's icon tile.
     */
    public static function iconBackground(?string $color): string
    {
        return self::ICON_BACKGROUNDS[$color] ?? self::ICON_BACKGROUNDS['primary'];
    }

    /**
     * The width of a field in the form's 12-column grid.
     */
    public static function span(int $columns): string
    {
        return self::SPANS[max(1, min(12, $columns))];
    }

    private function version(string $file): string
    {
        return self::$versions[$file] ??= is_file($path = self::sourcePath($file)) ? substr(md5_file($path), 0, 10) : '0';
    }
}
