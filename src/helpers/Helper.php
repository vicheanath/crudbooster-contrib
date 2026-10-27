<?php
/* 
| ---------------------------------------------------------------------------------------------------------------
| Main Helper of CRUDBooster
| Do not edit or modify this helper unless your modification will be replace if any update from CRUDBooster.
| 
| Homepage : http://crudbooster.com
| ---------------------------------------------------------------------------------------------------------------
|
*/


if(!function_exists('ends_with')) {
    /**
     * Laravel ends_with alternative
     * @param $text
     * @param $need
     * @return bool
     *
     * @deprecated 7.2 Use Str::endsWith($text, $need).
     */
    function ends_with($text, $need) {
        return \Illuminate\Support\Str::endsWith($text, $need);
    }
}

if(!function_exists('cbLang')) {
    /**
     * @param $key
     * @param array $replace
     * @param null $locale
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|string|null
     *
     * @deprecated 7.2 Use __('crudbooster::crudbooster.'.$key, $replace, $locale).
     */
    function cbLang($key, array $replace = [], $locale = null) {
        return trans("crudbooster::crudbooster.".$key, $replace, $locale);
    }
}

if(!function_exists('db')) {
    /**
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     *
     * @deprecated 7.2 Use DB::table($table), or an Eloquent model.
     */
    function db(string $table) {
        return \Illuminate\Support\Facades\DB::table($table);
    }
}

if(!function_exists('assetThumbnail')) {
    /**
     * @deprecated 7.2 Use ImageColumn in a resource, or asset() with your own thumbnail path.
     */
    function assetThumbnail($path) {
        $path = str_replace('uploads/','uploads_thumbnail/',$path);
        return asset($path);
    }
}

if(!function_exists('assetResize')) {
    /**
     * @deprecated 7.2 Use Intervention Image v3 (ImageManager::gd()->read($path)->cover($width, $height)), or
     *     ImageColumn::size().
     */
    function assetResize($path,$width,$height=null,$quality=70) {
        $basename = basename($path);
        $pathWithoutName = str_replace($basename, '', $path);
        $newLocation = $pathWithoutName.'/w_'.$width.'_h_'.$height.'_'.$basename;
        if(Storage::exists($newLocation)) {
            return asset($newLocation);
        }else{
            $img = Image::read(storage_path($path))->cover((int) $width, (int) ($height ?: $width));
            $img->save(storage_path($newLocation), quality: $quality);
            return asset($newLocation);
        }
    }
}

if(!function_exists('extract_unit')) {
    /*
    Credits: Bit Repository
    URL: http://www.bitrepository.com/extract-content-between-two-delimiters-with-php.html
    */
    /**
     * @deprecated 7.2 Use Str::between($string, $start, $end).
     */
    function extract_unit($string, $start, $end)
    {
        $pos = stripos($string, $start);
        $str = substr($string, $pos);
        $str_two = substr($str, strlen($start));
        $second_pos = stripos($str_two, $end);
        $str_three = substr($str_two, 0, $second_pos);
        $unit = trim($str_three); // remove whitespaces
        return $unit;
    }
}


if(!function_exists('now')) {
    /**
     * @deprecated 7.2 Use Laravel's now() (a Carbon instance). Laravel 12+ always defines it, so this fallback
     *     never loads.
     */
    function now() {
        return date('Y-m-d H:i:s');
    }
}

/* 
| --------------------------------------------------------------------------------------------------------------
| Get data from input post/get more simply
| --------------------------------------------------------------------------------------------------------------
| $name = name of input
|
*/

if(!function_exists('get_setting')) {
    /**
     * @param $key
     * @param null $default
     * @return bool
     *
     * @deprecated 7.2 Use app(SettingsRepositoryInterface::class)->get($key) ?: $default.
     */
    function get_setting($key, $default = null) {
        $setting = \crocodicstudio\crudbooster\helpers\CB::getSetting($key);
        $setting = ($setting)?:$default;
        return $setting;
    }
}

if(!function_exists('str_random')) {
    /**
     * @deprecated 7.2 Use Str::random($length).
     */
    function str_random($length = 16) {
        return \Illuminate\Support\Str::random($length);
    }
}

if(!function_exists('str_slug')) {
    /**
     * @deprecated 7.2 Use Str::slug($text, $separator, $language).
     */
    function str_slug($text, $separator = "-", $language = "en") {
        return \Illuminate\Support\Str::slug($text, $separator, $language);
    }
}

if(!function_exists('str_limit')) {
    // Removed from Laravel 6; grid columns and exports use it for 'str_limit'.
    /**
     * @deprecated 7.2 Use Str::limit($value, $limit, $end), or TextColumn::limit() in a resource.
     */
    function str_limit($value, $limit = 100, $end = '...') {
        return \Illuminate\Support\Str::limit((string) $value, (int) $limit, $end);
    }
}

if(!function_exists('g')) {
    /**
     * @param $key
     * @param null $default
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\Request|string
     *
     * @deprecated 7.2 Use request($key, $default).
     */
    function g($key, $default = null) {
        return request($key, $default);
    }
}

if(!function_exists('min_var_export')) {
    /**
     * @deprecated 7.2 Use var_export($input, true).
     */
    function min_var_export($input) {
        if(is_array($input)) {
            $buffer = [];
            foreach($input as $key => $value)
                $buffer[] = var_export($key, true)."=>".min_var_export($value);
            return "[".implode(",",$buffer)."]";
        } else
            return var_export($input, true);
    }
}

if(!function_exists('rrmdir')) {
    /*
    * http://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
    */
    /**
     * @deprecated 7.2 Use File::deleteDirectory($dir).
     */
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }
}

