<?php namespace crocodicstudio\crudbooster;

use crocodicstudio\crudbooster\commands\CrudboosterVersionCommand;
use crocodicstudio\crudbooster\commands\Mailqueues;
use crocodicstudio\crudbooster\database\seeds\CBSeeder;
use crocodicstudio\crudbooster\helpers\LegacyNoticeHandler;
use crocodicstudio\crudbooster\audit\AuditLogger;
use crocodicstudio\crudbooster\contracts\Audit\AuditLoggerInterface;
use crocodicstudio\crudbooster\contracts\Files\FileUploaderInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormProcessorInterface;
use crocodicstudio\crudbooster\contracts\Forms\FormValidatorInterface;
use crocodicstudio\crudbooster\contracts\Grid\GridFilterParserInterface;
use crocodicstudio\crudbooster\contracts\Grid\GridQueryBuilderInterface;
use crocodicstudio\crudbooster\contracts\Schema\SchemaInspectorInterface;
use crocodicstudio\crudbooster\contracts\Security\AuthenticationServiceInterface;
use crocodicstudio\crudbooster\contracts\Security\PermissionCheckerInterface;
use crocodicstudio\crudbooster\export\ExporterRegistry;
use crocodicstudio\crudbooster\files\FileUploader;
use crocodicstudio\crudbooster\forms\FieldHandlerRegistry;
use crocodicstudio\crudbooster\forms\FormProcessor;
use crocodicstudio\crudbooster\forms\FormValidator;
use crocodicstudio\crudbooster\grid\GridFilterParser;
use crocodicstudio\crudbooster\grid\GridQueryBuilder;
use crocodicstudio\crudbooster\schema\SchemaInspector;
use crocodicstudio\crudbooster\security\PermissionManager;
use crocodicstudio\crudbooster\security\SessionAuthenticationService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use crocodicstudio\crudbooster\commands\CrudboosterInstallationCommand;
use crocodicstudio\crudbooster\commands\CrudboosterUpdateCommand;
use Illuminate\Foundation\AliasLoader;
use App;

class CRUDBoosterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     * Call when after all packages has been loaded
     *
     * @return void
     */

    public function boot()
    {        
                                
        $this->loadViewsFrom(__DIR__.'/views', 'crudbooster');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/localization','crudbooster');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if($this->app->runningInConsole()) {
            $this->registerSeeder();
            $this->publishes([__DIR__.'/configs/crudbooster.php' => config_path('crudbooster.php')],'cb_config');
            $this->publishes([__DIR__.'/userfiles/controllers/CBHook.php' => app_path('Http/Controllers/CBHook.php')],'CBHook');
            $this->publishes([__DIR__.'/userfiles/controllers/AdminCmsUsersController.php' => app_path('Http/Controllers/AdminCmsUsersController.php')],'cb_user_controller');
            $this->publishes([__DIR__.'/assets'=>public_path('vendor/crudbooster')],'cb_asset');
        }

        $this->customValidation();

        if (config('crudbooster.LEGACY_NOTICE_COMPAT', true)) {
            LegacyNoticeHandler::register([__DIR__, config('view.compiled'), app_path('Http/Controllers')]);
        }
    }

    /**
     * Register the application services.
     * Call when this package is first time loaded
     *
     * @return void
     */
    public function register()
    {                                   
        require __DIR__.'/helpers/Helper.php';      

        $this->mergeConfigFrom(__DIR__.'/configs/crudbooster.php','crudbooster');

        $this->registerSingleton();

        if($this->app->runningInConsole()) {
            $this->commands('crudboosterinstall');
            $this->commands('crudboosterupdate');
            $this->commands('crudboosterVersionCommand');
            $this->commands('crudboosterMailQueue');
        }

        $loader = AliasLoader::getInstance();
        $loader->alias('PDF', 'Barryvdh\DomPDF\Facade\Pdf');
        $loader->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        $loader->alias('Image', 'crocodicstudio\crudbooster\helpers\CBImage');
        $loader->alias('CRUDBooster', 'crocodicstudio\crudbooster\helpers\CRUDBooster');
        $loader->alias('CB', 'crocodicstudio\crudbooster\helpers\CB');
    }
   
    private function registerSingleton()
    {
        $this->registerServices();

        $this->app->singleton('crudbooster', function ()
        {
            return true;
        });

        $this->app->singleton('crudboosterinstall',function() {
            return new CrudboosterInstallationCommand;
        });
        
        $this->app->singleton('crudboosterupdate',function() {
            return new CrudboosterUpdateCommand;
        });

        $this->app->singleton("crudboosterVersionCommand", function() {
            return new CrudboosterVersionCommand;
        });

        $this->app->singleton("crudboosterMailQueue", function() {
            return new Mailqueues;
        });
    }

    /**
     * Binds the contracts CBController and the CRUDBooster helper depend on. Apps replace any of
     * them by binding their own implementation, e.g. in their AppServiceProvider.
     */
    private function registerServices()
    {
        $this->app->bind(AuthenticationServiceInterface::class, SessionAuthenticationService::class);
        $this->app->bind(PermissionCheckerInterface::class, PermissionManager::class);
        $this->app->bind(AuditLoggerInterface::class, AuditLogger::class);
        $this->app->bind(SchemaInspectorInterface::class, SchemaInspector::class);
        $this->app->bind(GridFilterParserInterface::class, GridFilterParser::class);
        $this->app->bind(GridQueryBuilderInterface::class, GridQueryBuilder::class);
        $this->app->bind(FormProcessorInterface::class, FormProcessor::class);
        $this->app->bind(FormValidatorInterface::class, FormValidator::class);
        $this->app->bind(FileUploaderInterface::class, FileUploader::class);

        // Registries are shared so types and formats registered at boot reach every request.
        $this->app->singleton(FieldHandlerRegistry::class);
        $this->app->singleton(ExporterRegistry::class);
    }

    protected function registerSeeder()
    {
        // `db:seed --class=CBSeeder` resolves to Database\Seeders\CBSeeder on Laravel 8+.
        if (! class_exists('Database\\Seeders\\CBSeeder')) {
            class_alias(CBSeeder::class, 'Database\\Seeders\\CBSeeder');
        }

        $command = request()->server('argv', null);
        if (is_array($command)) {
            $command = implode(' ', $command);
            if ($command == "artisan db:seed") {
                Artisan::call('db:seed', ['--class' => CBSeeder::class]);
            }
        }
    }

    private function customValidation() {
        Validator::extend('alpha_spaces', function ($attribute, $value) {
            // This will only accept alpha and spaces.
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[\pL\s]+$/u', $value);
        },'The :attribute should be letters and spaces only');

        Validator::extend('alpha_num_spaces', function ($attribute, $value) {
            // This will only accept alphanumeric and spaces.
            return preg_match('/^[a-zA-Z0-9\s]+$/', $value);
        },'The :attribute should be alphanumeric characters and spaces only');
    }
}
