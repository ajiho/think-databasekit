<?php

namespace ajiho\IlluminateDatabase;



use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Query\Builder;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Facade;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use think\facade\Config;
use think\Paginator;
use ajiho\IlluminateDatabase\Console\FactoryMakeCommand;
use ajiho\IlluminateDatabase\Console\SeederMakeCommand;
use ajiho\IlluminateDatabase\Console\MigrateMakeCommand;
use ajiho\IlluminateDatabase\Console\SeedCommand;



class Application extends Container implements ApplicationContract
{

    const VERSION = '7';

    /**
     * 应用的基本路径
     * @var string
     */
    protected $basePath;
    protected $appPath;

    /**
     * 开发人员定义的自定义数据库路径。
     *
     * @var string
     */
    protected $databasePath;

    /**
     * @var int|string
     */
    protected $namespace;


    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->basePath = rtrim($basePath, '\/');
        }

        //illuminate-database.php配置文件内容
        $this->instance('config.idb', $this->getConfig());

        //基本的实例绑定
        $this->registerBaseBindings();
        //给模型和查询构造器增加自定义方法
        $this->addMethodsToBuilder();

    }

    protected function addMethodsToBuilder()
    {

        Builder::macro('tpPaginate', function ($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null): Paginator {

            $page = $page ?: Paginator::getCurrentPage($pageName);
            $total = $this->getCountForPagination();
            $results = $total ? json_decode(json_encode($this->forPage($page, $perPage)->get($columns)), true) : new \think\Collection([]);


            $config = [
                'var_page' => $pageName
            ];

            $config['path'] = $config['path'] ?? Paginator::getCurrentPath();


            return Paginator::make($results, $perPage, $page, $total, false, $config);


        });


        EloquentBuilder::macro('tpPaginate', function ($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null) {

            $page = $page ?: Paginator::getCurrentPage($pageName);
            $perPage = $perPage ?: $this->model->getPerPage();
            $results = ($total = $this->toBase()->getCountForPagination()) ? $this->forPage($page, $perPage)->get($columns)->toArray() : $this->model->newCollection();

            $config = [
                'var_page' => $pageName
            ];

            $config['path'] = $config['path'] ?? Paginator::getCurrentPath();

            return Paginator::make($results, $perPage, $page, $total, false, $config);

        });
    }

    protected function registerBaseBindings()
    {
        static::setInstance($this);


        $this->singleton(
            'events',
            function ($app) {
                return new Dispatcher($app);
            }
        );

        $this->singleton(
            \Illuminate\Contracts\Events\Dispatcher::class,
            function ($app) {
                return $app['events'];
            }
        );


        $this->singleton('db', function ($app) {

            $capsule = new Capsule;

            //添加连接
            foreach ($this['config.idb']['connections'] as $connectionName => $connection) {
                $capsule->addConnection($connection, $connectionName);
            }


            //设置默认数据库连接
            $capsule->getDatabaseManager()->setDefaultConnection($this['config.idb']['default']);

            $capsule->setEventDispatcher($app['events']);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule->getDatabaseManager();

        });

        //门脸类初始化
        Facade::setFacadeApplication($this);
    }


    public function registerCommands($stubsPath = null)
    {

        $stubsPath = $stubsPath ?: __DIR__ . DIRECTORY_SEPARATOR . 'stubs';

        //指令初始化
        $this->commandsInit($stubsPath);

        //数据库迁移仓库
        $repository = new DatabaseMigrationRepository($this['db'], $this['config.idb']['migrations']);

        $creator = new MigrationCreator($this['files'], $this['path.stubs']);
        $migrator = new Migrator($repository, $this['db'], $this['files']);

        $this['artisan']->setName('Artisan');
        $this['artisan']->add(new InstallCommand($repository));
        $this['artisan']->add(new MigrateCommand($migrator));
        $this['artisan']->add(new MigrateMakeCommand($creator, $this['composer']));
        $this['artisan']->add(new SeederMakeCommand($this['files'], $this['composer']));
        $this['artisan']->add(new FactoryMakeCommand($this['files']));
        $this['artisan']->add(new SeedCommand($this['db']));
        $this['artisan']->add(new WipeCommand());
        $this['artisan']->add(new FreshCommand());
        $this['artisan']->add(new RefreshCommand());
        $this['artisan']->add(new ResetCommand($migrator));
        $this['artisan']->add(new RollbackCommand($migrator));
        $this['artisan']->add(new StatusCommand($migrator));

    }


    protected function commandsInit($stubsPath)
    {
        //绑定自定义存根路径
        $this->instance('path.stubs', $stubsPath);

        //绑定一个file实例方便后续使用
        $this->singleton(
            'files',
            function () {
                return new Filesystem();
            }
        );

        $this->singleton('composer', function ($app) {
            return new Composer($app['files']);
        });

        //绑定所有的种子类到容器中
        if ($this['files']->isDirectory($this->databasePath('seeds'))) {//目录存在才绑定
            $files = $this['files']->files($this->databasePath('seeds'));
            foreach ($files as $file) {
                require_once $file->getPathname();
                $className = '\\' . str_replace('.php', '', $file->getFilename());
                $this->bind($className);
            }
        }


        //绑定工厂实例,初始化faker
        $this->bind(Factory::class, function ($app) {
            return Factory::construct(FakerFactory::create($this['config.idb']['faker_locale']), $app->databasePath('factories'));
        });

        //绑定artisan实例用于调用
        $this->instance('artisan', new ConsoleApplication($this, $this['events'], $this->version()));

    }


    /**
     * 运行指令封装方法
     * @param $commandName string 可用指令make:migration、
     * @param $arguments array 参数
     * @param $runningInConsole boolean 是否在控制台中执行
     * @param $outputExecTime boolean 是否输出执行时间
     * @return string
     */
    public function run($commandName, $arguments, $runningInConsole = false, $outputExecTime = false)
    {
        //找到指令
        $command = $this['artisan']->find($commandName);


        //输出对象
        $output = $runningInConsole ? new ConsoleOutput() : new BufferedOutput();

        //执行开始时间
        $start = microtime(true);

        try {
            $status = $command->run(new ArrayInput($arguments), $output);

            if ($runningInConsole === false) {//如果不是运行在cli下需要直接返回执行结果
                return $outputExecTime ? $output->fetch() . $this->getExecTime($start) : $output->fetch();
            }

            $outputExecTime && $output->writeln($this->getExecTime($start));


            return $status;

        } catch (\Exception $e) {

            if ($runningInConsole === false) {//如果不是运行在cli下需要直接返回异常信息
                return $outputExecTime ? $e->getMessage() . $this->getExecTime($start) : $e->getMessage();
            }

            //非cli情况直接输出错误信息到cmd上
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            //判断是否需要输出执行时间
            $outputExecTime && $output->writeln($this->getExecTime($start));
        }

    }

    private function getExecTime($startMicrotime)
    {
        return "执行时间:" . (microtime(true) - $startMicrotime) . " 秒";;
    }

    protected function getConfig()
    {
        $default = [
            //默认的数据库连接
            'default' => 'default',
            //数据库连接配置
            'connections' => [],
            //默认的迁移表名
            'migrations' => 'migrations',
            //faker本地化
            'faker_locale' => 'zh_CN'
        ];
        return array_merge($default, Config::get('illuminate-database'));
    }


    /**
     * 获取应用程序“app”目录的路径。
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
     * 获取应用程序的版本号。
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * 获取Laravel安装的基本路径。
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 获取引导程序目录的路径。
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取应用程序配置文件的路径
     *
     * @param string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取数据库目录的路径
     *
     * @param string $path Optionally, a path to append to the database path
     * @return string
     */
    public function databasePath($path = '')
    {
        return ($this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'db') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 获取资源目录的路径。
     *
     * @param string $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取存储目录的路径。(对应tp框架的runtime)
     *
     * @return string
     */
    public function storagePath()
    {
        // 无需实现
    }


    /**
     * 获取或检查当前应用程序环境。
     *
     * @param string|array $environments
     * @return string|bool
     */
    public function environment(...$environments)
    {
        // 无需实现
    }

    /**
     * 确定应用程序是否正在控制台中运行。
     *
     * @return bool
     */
    public function runningInConsole()
    {
        // 无需实现

    }

    /**
     * 确定应用程序是否正在运行单元测试。
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        // 无需实现
    }

    /**
     * 确定应用程序当前是否已停机进行维护。
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        // 无需实现
    }

    /**
     * 注册所有配置的提供程序。
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        // 无需实现
    }

    /**
     * 向应用程序注册服务提供商。
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @param bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        // 无需实现
    }

    /**
     * 注册延迟提供程序和服务。
     *
     * @param string $provider
     * @param string|null $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // 无需实现
    }

    /**
     * 从类名解析服务提供程序实例。
     *
     * @param string $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider($provider)
    {
        // 无需实现
    }

    /**
     * 启动应用程序的服务提供商。
     *
     * @return void
     */
    public function boot()
    {
        // 无需实现
    }

    /**
     * 注册一个新的启动侦听器。
     *
     * @param callable $callback
     * @return void
     */
    public function booting($callback)
    {
        // 无需实现
    }

    /**
     * 注册一个新的“已启动”侦听器。
     *
     * @param callable $callback
     * @return void
     */
    public function booted($callback)
    {
        // 无需实现
    }

    /**
     * 运行给定的引导程序类数组。
     *
     * @param array $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        // 无需实现
    }

    /**
     * 获取当前应用程序区域设置。
     *
     * @return string
     */
    public function getLocale()
    {
        // 无需实现
    }

    /**
     * 获取应用程序命名空间。
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
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

    /**
     * 获取已注册的服务提供程序实例（如果存在）。
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @return array
     */
    public function getProviders($provider)
    {
        // 无需实现
    }

    /**
     * 确定应用程序以前是否已启动。
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        // 无需实现
    }

    /**
     * 加载并启动所有剩余的延迟提供程序。
     *
     * @return void
     */
    public function loadDeferredProviders()
    {
        // 无需实现
    }

    /**
     * 设置当前应用程序区域设置。
     *
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        // 无需实现
    }

    /**
     * 确定是否已为应用程序禁用中间件。
     *
     * @return bool
     */
    public function shouldSkipMiddleware()
    {
        // 无需实现
    }

    /**
     * 终止应用程序。
     *
     * @return void
     */
    public function terminate()
    {
        // 无需实现
    }


}
