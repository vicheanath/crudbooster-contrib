<?php

namespace crocodicstudio\crudbooster\helpers;

use Cache;
use DB;
use Request;
use Route;
use Schema;
use Session;
use Storage;
use Validator;
use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Files\FileUploaderInterface;
use crocodicstudio\crudbooster\contracts\Generator\ApiGeneratorInterface;
use crocodicstudio\crudbooster\contracts\Generator\ModuleGeneratorInterface;
use crocodicstudio\crudbooster\contracts\Mail\TemplateMailerInterface;
use crocodicstudio\crudbooster\contracts\Navigation\MenuRepositoryInterface;
use crocodicstudio\crudbooster\contracts\Notifications\NotificationSenderInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;

class CRUDBooster
{
    /**
     *	Comma-delimited data output from the child table
     */
    public static function echoSelect2Mult($values, $table, $id, $name) {
        $values = explode(",", $values);
        return implode(", ", DB::table($table)->whereIn($id, $values)->pluck($name)->toArray());
        //implode(", ", DB::table("syudo_list_pokemons_types")->whereIn("id", explode(",", $row->type))->pluck("name")->toArray())

    }

    public static function uploadBase64($value, $id = null)
    {
        return app(FileUploaderInterface::class)->uploadBase64((string) $value, $id);
    }

    public static function uploadFile($name, $encrypt = false, $resize_width = null, $resize_height = null, $id = null)
    {
        return app(FileUploaderInterface::class)->upload($name, (bool) $encrypt, $resize_width, $resize_height, $id);
    }

    public static function getSetting($name)
    {
        return app(SettingsRepositoryInterface::class)->get((string) $name);
    }

    public static function insert($table, $data = [])
    {
        if (! $data['created_at']) {
            if (Schema::hasColumn($table, 'created_at')) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
        }

        if (DB::table($table)->insert($data)) {
            return $data['id'];
        } else {
            return false;
        }
    }

    public static function first($table, $id)
    {
        $table = self::parseSqlTable($table)['table'];
        if (is_array($id)) {
            $first = DB::table($table);
            foreach ($id as $k => $v) {
                $first->where($k, $v);
            }

            return $first->first();
        } else {
            $pk = self::pk($table);

            return DB::table($table)->where($pk, $id)->first();
        }
    }

    public static function get($table, $string_conditions = null, $orderby = null, $limit = null, $skip = null)
    {
        $table = self::parseSqlTable($table);
        $table = $table['table'];
        $query = DB::table($table);
        if ($string_conditions) {
            $query->whereraw($string_conditions);
        }
        if ($orderby) {
            $query->orderbyraw($orderby);
        }
        if ($limit) {
            $query->take($limit);
        }
        if ($skip) {
            $query->skip($skip);
        }

        return $query->get();
    }

    public static function me()
    {
        return DB::table(config('crudbooster.USER_TABLE'))->where('id', Session::get('admin_id'))->first();
    }

    public static function myId()
    {
        return app(AuthenticationServiceInterface::class)->currentUserId();
    }

    public static function isSuperadmin()
    {
        return app(AuthenticationServiceInterface::class)->isSuperadmin();
    }

    public static function myName()
    {
        return Session::get('admin_name');
    }

    public static function myPhoto()
    {
        return Session::get('admin_photo');
    }

    public static function myPrivilege()
    {
        $roles = Session::get('admin_privileges_roles');
        if ($roles) {
            foreach ($roles as $role) {
                if ($role->path == CRUDBooster::getModulePath()) {
                    return $role;
                }
            }
        }
    }

    public static function myPrivilegeId()
    {
        return Session::get('admin_privileges');
    }

    public static function myPrivilegeName()
    {
        return Session::get('admin_privileges_name');
    }

    public static function isLocked()
    {
        return Session::get('admin_lock');
    }

    public static function redirectBack($message, $type = 'warning')
    {

        if (Request::ajax()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => $message, 'message_type' => $type, 'redirect_url' => $_SERVER['HTTP_REFERER']]));
        } else {
            $resp = redirect()->back()->with(['message' => $message, 'message_type' => $type]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException($resp);
        }
    }

    public static function redirect($to, $message, $type = 'warning')
    {
        // Callers often pass $_SERVER['HTTP_REFERER'], which is missing on direct requests.
        $to = $to ?: url()->previous();

        if (Request::ajax()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json(['message' => $message, 'message_type' => $type, 'redirect_url' => $to]));
        } else {
            $resp = redirect($to)->with(['message' => $message, 'message_type' => $type]);
            throw new \Illuminate\Http\Exceptions\HttpResponseException($resp);
        }
    }

    public static function isView()
    {
        return app(PermissionCheckerInterface::class)->canView();
    }

    public static function isUpdate()
    {
        return app(PermissionCheckerInterface::class)->canUpdate();
    }

    public static function isCreate()
    {
        return app(PermissionCheckerInterface::class)->canCreate();
    }

    public static function isRead()
    {
        return app(PermissionCheckerInterface::class)->canRead();
    }

    public static function isDelete()
    {
        return app(PermissionCheckerInterface::class)->canDelete();
    }

    public static function isCRUD()
    {
        if (self::isSuperadmin()) {
            return true;
        }

        $session = Session::get('admin_privileges_roles');
        foreach ($session as $v) {
            if ($v->path == self::getModulePath()) {
                if ($v->is_visible && $v->is_create && $v->is_read && $v->is_edit && $v->is_delete) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public static function getCurrentModule()
    {
        $modulepath = self::getModulePath();

        if (Cache::has('moduls_'.$modulepath)) {
            return Cache::get('moduls_'.$modulepath);
        } else {

            $module = DB::table('cms_moduls')->where('path', self::getModulePath())->first();

            //supply modulpath instead of $module incase where user decides to create form and custom url that does not exist in cms_moduls table.
            return ($module)?:$modulepath;
        }
    }

    public static function getCurrentDashboardId()
    {
        if (Request::get('d') != null) {
            Session::put('currentDashboardId', Request::get('d'));
            Session::put('currentMenuId', 0);

            return Request::get('d');
        } else {
            return Session::get('currentDashboardId');
        }
    }

    public static function getCurrentMenuId()
    {
        if (Request::get('m') != null) {
            Session::put('currentMenuId', Request::get('m'));
            Session::put('currentDashboardId', 0);

            return Request::get('m');
        } else {
            return Session::get('currentMenuId');
        }
    }

    /**
     * Absolute path inside the package's src/ directory, wherever Composer installed it.
     */
    public static function packagePath($path = '')
    {
        return dirname(__DIR__).($path !== '' ? '/'.ltrim($path, '/') : '');
    }

    public static function sidebarDashboard()
    {
        return app(MenuRepositoryInterface::class)->dashboard();
    }

    public static function sidebarMenu()
    {
        return app(MenuRepositoryInterface::class)->sidebar();
    }

    public static function deleteConfirm($redirectTo)
    {
        $legacy = "swal({   
				title: \"".cbLang('delete_title_confirm')."\",   
				text: \"".cbLang('delete_description_confirm')."\",   
				type: \"warning\",   
				showCancelButton: true,   
				confirmButtonColor: \"#ff0000\",   
				confirmButtonText: \"".cbLang('confirmation_yes')."\",  
				cancelButtonText: \"".cbLang('confirmation_no')."\",  
				closeOnConfirm: false }, 
				function(){  location.href=\"$redirectTo\" });";

        // Pages that load SweetAlert2 (window.Swal) get its API; the bundled sweetalert 1.x keeps the legacy call.
        $json = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP;
        $options = json_encode(array_merge([
            'title' => cbLang('delete_title_confirm'),
            'text' => cbLang('delete_description_confirm'),
            'icon' => 'warning',
            'showCancelButton' => true,
            'confirmButtonColor' => '#ff0000',
            'confirmButtonText' => cbLang('confirmation_yes'),
            'cancelButtonText' => cbLang('confirmation_no'),
        ], (array) config('crudbooster.SWEETALERT2_DELETE_OPTIONS', [])), $json);
        $url = json_encode((string) $redirectTo, $json);

        echo "if (window.Swal && Swal.fire) { Swal.fire($options).then(function (result) { if (result.isConfirmed) { location.href = $url; } }); } else { $legacy }";
    }

    public static function getModulePath()
    {
        // Check to position of admin_path
        if(config("crudbooster.ADMIN_PATH")) {
            $adminPathSegments = explode('/', Request::path());
            $no = 1;
            foreach($adminPathSegments as $path) {
                if($path == config("crudbooster.ADMIN_PATH")) {
                    $segment = $no+1;
                    break;
                }
                $no++;
            }
        } else {
            $segment = 1;
        }

        return Request::segment($segment);
    }

    public static function mainpath($path = null)
    {

        $controllername = str_replace(["\crocodicstudio\crudbooster\controllers\\", "App\Http\Controllers\\"], "", strtok(Route::currentRouteAction(), '@'));
        $route_url = route($controllername.'GetIndex');

        if ($path) {
            if (substr($path, 0, 1) == '?') {
                return trim($route_url, '/').$path;
            } else {
                return $route_url.'/'.$path;
            }
        } else {
            return trim($route_url, '/');
        }
    }

    public static function adminPath($path = null)
    {
        return url(config('crudbooster.ADMIN_PATH').'/'.$path);
    }

    public static function getCurrentId()
    {
        $id = Session::get('current_row_id');
        $id = intval($id);
        $id = (! $id) ? Request::segment(4) : $id;
        $id = intval($id);

        return $id;
    }

    public static function getCurrentMethod()
    {
        $action = str_replace("App\Http\Controllers", "", Route::currentRouteAction());
        $atloc = strpos($action, '@') + 1;
        $method = substr($action, $atloc);

        return $method;
    }

    public static function clearCache($name)
    {
        if (Cache::forget($name)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isColumnNULL($table, $field)
    {
        return app(SchemaInspectorInterface::class)->isNullable((string) $table, (string) $field);
    }

    public static function getFieldType($table, $field)
    {
        return app(SchemaInspectorInterface::class)->columnType((string) $table, (string) $field);
    }

    public static function getValueFilter($field)
    {
        $filter = Request::get('filter_column');
        if ($filter[$field]) {
            return $filter[$field]['value'];
        }
    }

    public static function getSortingFilter($field)
    {
        $filter = Request::get('filter_column');
        if ($filter[$field]) {
            return $filter[$field]['sorting'];
        }
    }

    public static function getTypeFilter($field)
    {
        $filter = Request::get('filter_column');
        if ($filter[$field]) {
            return $filter[$field]['type'];
        }
    }

    public static function stringBetween($string, $start, $end)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public static function timeAgo($datetime_to, $datetime_from = null, $full = false)
    {
        $datetime_from = ($datetime_from) ?: date('Y-m-d H:i:s');
        $now = new \DateTime;
        if ($datetime_from != '') {
            $now = new \DateTime($datetime_from);
        }
        $ago = new \DateTime($datetime_to);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k.' '.$v.($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (! $full) {
            $string = array_slice($string, 0, 1);
        }

        return $string ? implode(', ', $string).' ' : 'just now';
    }

    public static function mailer()
    {
        return app(TemplateMailerInterface::class)->mailer();
    }

    public static function sendEmailQueue($queue)
    {
        app(TemplateMailerInterface::class)->sendQueued($queue);
    }

    public static function sendEmail($config = [])
    {
        return app(TemplateMailerInterface::class)->send((array) $config);
    }

    public static function valid($arr = [], $type = 'json')
    {
        $input_arr = Request::all();

        foreach ($arr as $a => $b) {
            if (is_int($a)) {
                $arr[$b] = 'required';
            } else {
                $arr[$a] = $b;
            }
        }

        $validator = Validator::make($input_arr, $arr);

        if ($validator->fails()) {
            $message = $validator->errors()->all();

            if ($type == 'json') {
                $result = [];
                $result['api_status'] = 0;
                $result['api_message'] = implode(', ', $message);
                $res = response()->json($result, 200);
                throw new \Illuminate\Http\Exceptions\HttpResponseException($res);
            } else {
                $res = redirect()->back()->with(['message' => implode('<br/>', $message), 'message_type' => 'warning'])->withInput();
                throw new \Illuminate\Http\Exceptions\HttpResponseException($res);
            }
        }
    }

    public static function parseSqlTable($table)
    {

        $f = explode('.', $table);

        if (count($f) == 1) {
            return ["table" => $f[0], "database" => config('crudbooster.MAIN_DB_DATABASE')];
        } elseif (count($f) == 2) {
            return ["database" => $f[0], "table" => $f[1]];
        } elseif (count($f) == 3) {
            return ["table" => $f[0], "schema" => $f[1], "table" => $f[2]];
        }

        return false;
    }

    public static function putCache($section, $cache_name, $cache_value)
    {
        if (Cache::has($section)) {
            $cache_open = Cache::get($section);
        } else {
            Cache::forever($section, []);
            $cache_open = Cache::get($section);
        }
        $cache_open[$cache_name] = $cache_value;
        Cache::forever($section, $cache_open);

        return true;
    }

    public static function getCache($section, $cache_name)
    {

        if (Cache::has($section)) {
            $cache_open = Cache::get($section);

            return $cache_open[$cache_name];
        } else {
            return false;
        }
    }

    public static function flushCache()
    {
        Cache::flush();
    }

    public static function forgetCache($section, $cache_name)
    {
        if (Cache::has($section)) {
            $open = Cache::get($section);
            unset($open[$cache_name]);
            Cache::forever($section, $open);

            return true;
        } else {
            return false;
        }
    }

    public static function pk($table)
    {
        return self::findPrimaryKey($table);
    }

//     public static function findPrimaryKey($table)
//     {
//         if (! $table) {
//             return 'id';
//         }

//         if (self::getCache('table_'.$table, 'primary_key')) {
//             return self::getCache('table_'.$table, 'primary_key');
//         }
//         $table = CRUDBooster::parseSqlTable($table);

//         if (! $table['table']) {
//             throw new \Exception("parseSqlTable can't determine the table");
//         }
//         $query = config('database.connections.'.config('database.default').'.driver') == 'pgsql' ? "select * from information_schema.key_column_usage WHERE TABLE_NAME = '$table[table]'" : "select * from information_schema.COLUMNS where TABLE_SCHEMA = '$table[database]' and TABLE_NAME = '$table[table]' and COLUMN_KEY = 'PRI'";
//         $keys = DB::select($query);
//         $primary_key = $keys[0]->COLUMN_NAME;
//         if ($primary_key) {
//             self::putCache('table_'.$table, 'primary_key', $primary_key);

//             return $primary_key;
//         } else {
//             return 'id';
//         }
//     }

    public static function findPrimaryKey($table)
    {
        return app(SchemaInspectorInterface::class)->primaryKey((string) $table);
    }

    public static function newId($table)
    {
        $key = CRUDBooster::findPrimaryKey($table);
        $id = DB::table($table)->max($key) + 1;

        return $id;
    }

    public static function isColumnExists($table, $field)
    {

        if (! $table) {
            throw new Exception("\$table is empty !", 1);
        }
        if (! $field) {
            throw new Exception("\$field is empty !", 1);
        }

        $table = CRUDBooster::parseSqlTable($table);

        // if(self::getCache('table_'.$table,'column_'.$field)) {
        // 	return self::getCache('table_'.$table,'column_'.$field);
        // }

        if (app(SchemaInspectorInterface::class)->hasColumn($table['table'], $field)) {
            // self::putCache('table_'.$table,'column_'.$field,1);
            return true;
        } else {
            // self::putCache('table_'.$table,'column_'.$field,0);
            return false;
        }
    }

    public static function getForeignKey($parent_table, $child_table)
    {
        $parent_table = CRUDBooster::parseSqlTable($parent_table)['table'];
        $child_table = CRUDBooster::parseSqlTable($child_table)['table'];
        if (Schema::hasColumn($child_table, 'id_'.$parent_table)) {
            return 'id_'.$parent_table;
        } else {
            return $parent_table.'_id';
        }
    }

    public static function getTableForeignKey($fieldName)
    {
        $table = null;
        if (substr($fieldName, 0, 3) == 'id_') {
            $table = substr($fieldName, 3);
        } elseif (substr($fieldName, -3) == '_id') {
            $table = substr($fieldName, 0, (strlen($fieldName) - 3));
        }

        return $table;
    }

    public static function isForeignKey($fieldName)
    {
        if (substr($fieldName, 0, 3) == 'id_') {
            $table = substr($fieldName, 3);
        } elseif (substr($fieldName, -3) == '_id') {
            $table = substr($fieldName, 0, (strlen($fieldName) - 3));
        }

        if (Cache::has('isForeignKey_'.$fieldName)) {
            return Cache::get('isForeignKey_'.$fieldName);
        } else {
            if ($table) {
                $hasTable = Schema::hasTable($table);
                if ($hasTable) {
                    Cache::forever('isForeignKey_'.$fieldName, true);

                    return true;
                } else {
                    Cache::forever('isForeignKey_'.$fieldName, false);

                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public static function urlFilterColumn($key, $type, $value = '', $singleSorting = true)
    {
        $params = Request::all();
        $mainpath = trim(self::mainpath(), '/');

        if ($params['filter_column'] && $singleSorting) {
            foreach ($params['filter_column'] as $k => $filter) {
                foreach ($filter as $t => $val) {
                    if ($t == 'sorting') {
                        unset($params['filter_column'][$k]['sorting']);
                    }
                }
            }
        }

        $params['filter_column'][$key][$type] = $value;

        if (isset($params)) {
            return $mainpath.'?'.http_build_query($params);
        } else {
            return $mainpath.'?filter_column['.$key.']['.$type.']='.$value;
        }
    }

    public static function insertLog($description, $details = '')
    {
        app(AuditLoggerInterface::class)->log((string) $description, (string) $details);
    }

    public static function referer()
    {
        return Request::server('HTTP_REFERER');
    }

    public static function listTables()
    {
        $tables = [];
        $multiple_db = config('crudbooster.MULTIPLE_DATABASE_MODULE');
        $multiple_db = ($multiple_db) ? $multiple_db : [];
        $db_database = config('crudbooster.MAIN_DB_DATABASE');

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return array_map(function ($name) {
                return (object) ['TABLE_NAME' => $name];
            }, Schema::getTableListing(null, false));
        }

        if ($multiple_db) {
            try {
                $multiple_db[] = config('crudbooster.MAIN_DB_DATABASE');
                $query_table_schema = implode("','", $multiple_db);
                $tables = DB::select("SELECT CONCAT(TABLE_SCHEMA,'.',TABLE_NAME) FROM INFORMATION_SCHEMA.Tables WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA != 'mysql' AND TABLE_SCHEMA != 'performance_schema' AND TABLE_SCHEMA != 'information_schema' AND TABLE_SCHEMA != 'phpmyadmin' AND TABLE_SCHEMA IN ('$query_table_schema')");
            } catch (\Exception $e) {
                $tables = [];
            }
        } else {
            try {
                $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.Tables WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = '".$db_database."'");
            } catch (\Exception $e) {
                $tables = [];
            }
        }

        return $tables;
    }

    public static function getUrlParameters($exception = null)
    {
        @$get = $_GET;
        $inputhtml = '';

        if ($get) {

            if (is_array($exception)) {
                foreach ($exception as $e) {
                    unset($get[$e]);
                }
            }

            $string_parameters = http_build_query($get);
            $string_parameters_array = explode('&', $string_parameters);
            foreach ($string_parameters_array as $s) {
                $part = explode('=', $s);
                $name = urldecode($part[0]);
                $value = urldecode($part[1]);
                if ($name) {
                    $inputhtml .= "<input type='hidden' name='$name' value='$value'/>\n";
                }
            }
        }

        return $inputhtml;
    }

    public static function authAPI()
    {
        $allowedUserAgent = config('crudbooster.API_USER_AGENT_ALLOWED');
        $user_agent = Request::header('User-Agent');
        $authorization = Request::header('Authorization');

        if ($allowedUserAgent && count($allowedUserAgent)) {
            $userAgentValid = false;
            foreach ($allowedUserAgent as $a) {
                if (stripos($user_agent, $a) !== false) {
                    $userAgentValid = true;
                    break;
                }
            }
            if ($userAgentValid == false) {
                $result['api_status'] = 0;
                $result['api_message'] = "THE DEVICE AGENT IS INVALID";
                $res = response()->json($result, 400);
                throw new \Illuminate\Http\Exceptions\HttpResponseException($res);
            }
        }

        // Strip the "Bearer " prefix; ltrim() with a character list also ate leading B/e/a/r from the token.
        $accessToken = preg_replace('/^Bearer\s+/i', '', (string) $authorization);
        $accessTokenData = Cache::get("api_token_".$accessToken);
        if(!$accessTokenData) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
                'api_status'=> 0,
                'api_message'=> 'Forbidden Access!'
            ], 403));
        }
    }

    public static function sendNotification($config = [])
    {
        app(NotificationSenderInterface::class)->send(
            (string) ($config['content'] ?? ''),
            $config['to'] ?? null,
            (array) ($config['id_cms_users'] ?? [])
        );

        return true;
    }

    public static function sendFCM($regID, $data)
    {
        if (! $data['title'] || ! $data['content']) {
            return 'title , content null !';
        }

        return FCM::send((array) $regID, $data);
    }

    public static function getTableColumns($table)
    {
        $table = CRUDBooster::parseSqlTable($table);
        return app(SchemaInspectorInterface::class)->columns(($table['database'] ? $table['database'].'.' : '').$table['table']);
    }

    public static function getNameTable($columns)
    {
        return app(ModuleGeneratorInterface::class)->titleColumn((array) $columns);
    }

    public static function isExistsController($table)
    {
        $controllername = ucwords(str_replace('_', ' ', $table));
        $controllername = str_replace(' ', '', $controllername).'Controller';
        $path = base_path("app/Http/Controllers/");
        $path2 = base_path("app/Http/Controllers/ControllerMaster/");
        if (file_exists($path.'Admin'.$controllername.'.php') || file_exists($path2.'Admin'.$controllername.'.php') || file_exists($path2.$controllername.'.php')) {
            return true;
        } else {
            return false;
        }
    }

    public static function generateAPI($controller_name, $table_name, $permalink, $method_type = 'post')
    {
        app(ApiGeneratorInterface::class)->generate((string) $controller_name, (string) $table_name, (string) $permalink, (string) $method_type);
    }

    public static function generateController($table, $name = null)
    {
        return app(ModuleGeneratorInterface::class)->generate((string) $table, $name ? (string) $name : null);
    }

    /*
    | --------------------------------------------------------------------------------------------------------------
    | Alternate route for Laravel Route::controller
    | --------------------------------------------------------------------------------------------------------------
    | $prefix       = path of route
    | $controller   = controller name
    | $namespace    = namespace of controller (optional)
    |
    */
    public static function routeController($prefix, $controller, $namespace = null)
    {

        $prefix = trim($prefix, '/').'/';

        $namespace = ($namespace) ?: 'App\Http\Controllers';

        try {
            Route::get($prefix, ['uses' => $controller.'@getIndex', 'as' => $controller.'GetIndex']);

            $controller_class = new \ReflectionClass($namespace.'\\'.$controller);
            $controller_methods = $controller_class->getMethods(\ReflectionMethod::IS_PUBLIC);
            $wildcards = '/{one?}/{two?}/{three?}/{four?}/{five?}';
            foreach ($controller_methods as $method) {

                if ($method->class != 'Illuminate\Routing\Controller' && $method->name != 'getIndex') {
                    if (substr($method->name, 0, 3) == 'get') {
                        $method_name = substr($method->name, 3);
                        $slug = array_filter(preg_split('/(?=[A-Z])/', $method_name));
                        $slug = strtolower(implode('-', $slug));
                        $slug = ($slug == 'index') ? '' : $slug;
                        Route::get($prefix.$slug.$wildcards, ['uses' => $controller.'@'.$method->name, 'as' => $controller.'Get'.$method_name]);
                    } elseif (substr($method->name, 0, 4) == 'post') {
                        $method_name = substr($method->name, 4);
                        $slug = array_filter(preg_split('/(?=[A-Z])/', $method_name));
                        Route::post($prefix.strtolower(implode('-', $slug)).$wildcards, [
                            'uses' => $controller.'@'.$method->name,
                            'as' => $controller.'Post'.$method_name,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }
}
