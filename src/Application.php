<?php

namespace ajiho\databasekit;

use ajiho\databasekit\console\FactoryMakeCommand;
use ajiho\databasekit\console\MigrateMakeCommand;
use ajiho\databasekit\console\SeedCommand;
use ajiho\databasekit\console\SeederMakeCommand;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Container\Container;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Facade;
use RuntimeException;
use Illuminate\Console\Application as ConsoleApp;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use think\App;
use Illuminate\Support\Composer;
use Illuminate\Database\Eloquent\Factory;
use Faker\Factory as FakerFactory;

class Application extends Container implements ApplicationContract
{

    const VERSION = '7.30.6';


    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The custom application path defined by the developer.
     *
     * @var string
     */
    protected $appPath;

    /**
     * The custom database path defined by the developer.
     *
     * @var string
     */
    protected $databasePath;

    /**
     * 应用命名空间
     *
     * @var string
     */
    protected $namespace;


    public function __construct(App $app, $basePath = null)
    {


        if ($basePath) {
            $this->basePath = rtrim($basePath, '\/');
        }


        $this->bindPathsInContainer();

        $this->registerBaseBindings($app);

        //注册迁移相关的服务
        $this->registerMigrationBindings();


        $this->bindSeedsInContainer();

        //注册所有的命令
        $this->registerCommands();

        Facade::setFacadeApplication($this);

    }

    public function bindSeedsInContainer()
    {
        $path = $this->databasePath('seeds');
        if ($this['files']->isDirectory($path)) {//目录存在才绑定
            $files = $this['files']->files($path);
            foreach ($files as $file) {
                require_once $file->getPathname();
                $className = '\\' . str_replace('.php', '', $file->getFilename());
                $this->bind($className);
            }
        }
    }

    public function bindPathsInContainer()
    {

        $stubsPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        $this->instance('path.stubs', $stubsPath);
        $this->instance('path.stubs.seeder.database', $stubsPath . 'seeder.database.stub');
        $this->instance('path.stubs.factory', $stubsPath . 'factory.stub');
        $this->instance('path.stubs.migration.create', $stubsPath . 'migration.create.stub');
        $this->instance('path.stubs.migration', $stubsPath . 'migration.stub');
        $this->instance('path.stubs.migration.update', $stubsPath . 'migration.update.stub');
        $this->instance('path.stubs.model.pivot', $stubsPath . 'model.pivot.stub');
        $this->instance('path.stubs.model', $stubsPath . 'model.stub');
        $this->instance('path.stubs.seeder', $stubsPath . 'seeder.stub');

    }


    public function registerBaseBindings($app)
    {

        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance('app.think', $app);


        $this->singleton('db', function ($app) {
            return $app['app.think']->get('capsule')->getDatabaseManager();
        });


        $this->instance('files', new Filesystem());


        $this->singleton('composer', function ($app) {
            return new Composer($app['files']);
        });

        $this->singleton(
            'events',
            function ($app) {
                return new Dispatcher($app);
            }
        );

        //绑定工厂实例,初始化faker
        $this->bind(Factory::class, function ($app) {
            $locale = $app['app.think']->config->get('databasekit.faker_locale');
            return Factory::construct(FakerFactory::create($locale), $app->databasePath('factories'));
        });


        $this->instance('app.console', new ConsoleApp($this, $this['events'], $this->version()));
    }


    public function registerMigrationBindings()
    {

        //注册迁移存储库服务。
        $this->singleton('migration.repository', function ($app) {

            //迁移表名
            $table = $app['app.think']->config->get('databasekit.migrations');

            return new DatabaseMigrationRepository($app['db'], $table);
        });


        //注册迁移器服务
        $this->singleton('migrator', function ($app) {

            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });


        //注册迁移创建者。
        $this->singleton('migration.creator', function ($app) {
            return new MigrationCreator($app['files'], $app['path.stubs']);
        });
    }


    public function registerCommands()
    {
        $console = $this['app.console'];
        $console->add(new InstallCommand($this['migration.repository']));
        $console->add(new MigrateCommand($this['migrator']));
        $console->add(new MigrateMakeCommand($this['migration.creator'], $this['composer']));
        $console->add(new SeederMakeCommand($this['files'], $this['composer']));
        $console->add(new FactoryMakeCommand($this['files']));
        $console->add(new SeedCommand($this['db']));
        $console->add(new WipeCommand());
        $console->add(new FreshCommand());
        $console->add(new RefreshCommand());
        $console->add(new ResetCommand($this['migrator']));
        $console->add(new RollbackCommand($this['migrator']));
        $console->add(new StatusCommand($this['migrator']));
    }


    public function databasePath($path = '')
    {
        return ($this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'database') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path
     * @return string
     */
    public function path($path = '')
    {
        $appPath = $this->appPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'app';

        return $appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace(): string
    {
        if (!is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach ((array)data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array)$path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }


    public function bootstrapPath($path = '')
    {
        // TODO: Implement bootstrapPath() method.
    }

    public function configPath($path = '')
    {
        // TODO: Implement configPath() method.
    }

    public function resourcePath($path = '')
    {
        // TODO: Implement resourcePath() method.
    }

    public function storagePath()
    {
        // TODO: Implement storagePath() method.
    }

    public function environment(...$environments)
    {
        // TODO: Implement environment() method.
    }

    public function runningInConsole()
    {
        // TODO: Implement runningInConsole() method.
    }

    public function runningUnitTests()
    {
        // TODO: Implement runningUnitTests() method.
    }

    public function isDownForMaintenance()
    {
        // TODO: Implement isDownForMaintenance() method.
    }

    public function registerConfiguredProviders()
    {
        // TODO: Implement registerConfiguredProviders() method.
    }

    public function register($provider, $force = false)
    {
        // TODO: Implement register() method.
    }

    public function registerDeferredProvider($provider, $service = null)
    {
        // TODO: Implement registerDeferredProvider() method.
    }

    public function resolveProvider($provider)
    {
        // TODO: Implement resolveProvider() method.
    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function booting($callback)
    {
        // TODO: Implement booting() method.
    }

    public function booted($callback)
    {
        // TODO: Implement booted() method.
    }

    public function bootstrapWith(array $bootstrappers)
    {
        // TODO: Implement bootstrapWith() method.
    }

    public function getLocale()
    {
        // TODO: Implement getLocale() method.
    }

    public function getProviders($provider)
    {
        // TODO: Implement getProviders() method.
    }

    public function hasBeenBootstrapped()
    {
        // TODO: Implement hasBeenBootstrapped() method.
    }

    public function loadDeferredProviders()
    {
        // TODO: Implement loadDeferredProviders() method.
    }

    public function setLocale($locale)
    {
        // TODO: Implement setLocale() method.
    }

    public function shouldSkipMiddleware()
    {
        // TODO: Implement shouldSkipMiddleware() method.
    }

    public function terminate()
    {
        // TODO: Implement terminate() method.
    }
}
