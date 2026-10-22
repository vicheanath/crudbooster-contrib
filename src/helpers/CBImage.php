<?php

namespace crocodicstudio\crudbooster\helpers;

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Thin wrapper around Intervention Image v3, registered as the `Image` alias.
 * Replaces the removed Intervention\Image\ImageManagerStatic (v2).
 */
class CBImage
{
    private static $manager;

    public static function manager(): ImageManager
    {
        if (! self::$manager) {
            self::$manager = extension_loaded('imagick') ? ImageManager::imagick() : ImageManager::gd();
        }

        return self::$manager;
    }

    public static function read($input): ImageInterface
    {
        return self::manager()->read($input);
    }

    /**
     * @deprecated v2 name kept so `Image::make($path)` in user code still loads the image. Use read().
     */
    public static function make($input): ImageInterface
    {
        return self::read($input);
    }
}
