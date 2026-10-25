<?php

namespace CrudBooster\Themes;

use Illuminate\Support\HtmlString;

/**
 * Resource pages in a Tailwind CSS layout, with React components for the form inputs. The
 * compiled assets ship in src/assets/panel and are published with the "cb_asset" tag.
 *
 * The class lists below are written out in full so Tailwind finds them when it scans this file.
 */
class TailwindTheme extends Theme
{
    public const ASSET_DIRECTORY = 'vendor/crudbooster/panel';

    private const BADGES = [
        'primary' => 'bg-brand-50 text-brand-700 ring-brand-600/20 dark:bg-brand-500/15 dark:text-brand-300 dark:ring-brand-400/30',
        'secondary' => 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-white/10 dark:text-gray-300 dark:ring-white/15',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-400/30',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-400/30',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/30',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-400/30',
    ];

    private const BUTTONS = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-500 focus-visible:outline-brand-600',
        'secondary' => 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-white/10 dark:text-white dark:ring-white/15 dark:hover:bg-white/15',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-500 focus-visible:outline-emerald-600',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus-visible:outline-red-600',
        'warning' => 'bg-amber-500 text-white hover:bg-amber-400 focus-visible:outline-amber-500',
        'info' => 'bg-sky-600 text-white hover:bg-sky-500 focus-visible:outline-sky-600',
    ];

    private const TEXT = [
        'primary' => 'text-brand-600 dark:text-brand-400',
        'secondary' => 'text-gray-500 dark:text-gray-400',
        'success' => 'text-emerald-600 dark:text-emerald-400',
        'danger' => 'text-red-600 dark:text-red-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'info' => 'text-sky-600 dark:text-sky-400',
    ];

    private const ICON_BACKGROUNDS = [
        'primary' => 'bg-brand-600',
        'secondary' => 'bg-gray-500',
        'success' => 'bg-emerald-600',
        'danger' => 'bg-red-600',
        'warning' => 'bg-amber-500',
        'info' => 'bg-sky-600',
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
     * Classes for a coloured badge: primary, secondary, success, danger, warning or info.
     */
    public static function badge(?string $color): string
    {
        return self::BADGES[$color] ?? self::BADGES['secondary'];
    }

    public static function button(?string $color): string
    {
        return self::BUTTONS[$color] ?? self::BUTTONS['secondary'];
    }

    public static function text(?string $color): string
    {
        return self::TEXT[$color] ?? self::TEXT['secondary'];
    }

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
