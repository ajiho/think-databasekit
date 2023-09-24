<?php

namespace ajiho\IlluminateDatabase;

use ajiho\IlluminateDatabase\Console\DatabaseSeederMakeCommand;
use ajiho\IlluminateDatabase\Console\FactoryMakeCommand;
use ajiho\IlluminateDatabase\Console\SeederMakeCommand;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use think\Paginator;

class Application extends BaseApplication
{

    /**
     * think-illuminate-database 配置
     * @var mixed
     */
    protected $config;

    public function __construct($databaseManager, $dispatcher)
    {
        parent::__construct();


        //读取配置文件
        $this->config = config('illuminate-database');

        $this->singleton(
            'events',
            function () use ($dispatcher) {
                return $dispatcher;
            }
        );
        $this->singleton('db', function () use ($databaseManager) {
            return $databaseManager;
        });

        //基本的实例绑定
        $this->registerBaseBindings();

        //注册所有的指令
        $this->registerCommands();

        //给模型和查询构造器增加自定义方法
        $this->addMethodsToBuilder();

    }


    /**
     * 将基本绑定注册到容器中。
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        //门脸类初始化
        Facade::setFacadeApplication($this);

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


        $this->singleton(
            \Illuminate\Contracts\Events\Dispatcher::class,
            function ($app) {
                return $app['events'];
            }
        );


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
            //获取faker本地化配置
            $faker_locale = isset($app->config['faker_locale']) && is_string($app->config['faker_locale']) ? $app->config['faker_locale'] : 'zh_CN';
            return Factory::construct(\Faker\Factory::create($faker_locale), $app->databasePath('factories'));
        });

        //绑定artisan实例用于调用
        $this->instance('artisan', new \Illuminate\Console\Application($this, $this['events'], $this->version()));

    }


    protected function registerCommands()
    {

        //从配置文件中获取到默认的表名
        $migrations_table_name = isset($app->config['migrations']) && is_string($app->config['migrations']) ? $app->config['migrations'] : 'migrations';


        //数据库迁移仓库
        $repository = new DatabaseMigrationRepository($this['db'], $migrations_table_name);


        //自定义迁移存根文件
        $stubs = app_path() . 'stubs';
        $creator = new MigrationCreator($this['files'], $stubs);
        $migrator = new Migrator($repository, $this['db'], $this['files']);

        $this['artisan']->setName('Artisan');
        $this['artisan']->add(new InstallCommand($repository));
        $this['artisan']->add(new MigrateCommand($migrator));
        $this['artisan']->add(new MigrateMakeCommand($creator, $this['composer']));
        $this['artisan']->add(new SeederMakeCommand($this['files'], $this['composer']));
        $this['artisan']->add(new DatabaseSeederMakeCommand($this['files'], $this['composer']));
        $this['artisan']->add(new FactoryMakeCommand($this['files']));
        $this['artisan']->add(new SeedCommand($this['db']));
        $this['artisan']->add(new WipeCommand());
        $this['artisan']->add(new FreshCommand());
        $this['artisan']->add(new RefreshCommand());
        $this['artisan']->add(new ResetCommand($migrator));
        $this['artisan']->add(new RollbackCommand($migrator));
        $this['artisan']->add(new StatusCommand($migrator));
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


    /**
     * 运行artisan指令
     * @param $command
     * @param $arguments
     * @return string
     */
    public function runCommand($command, $arguments)
    {
        $output = new BufferedOutput();

        $input = new ArrayInput($arguments);

        $command->run($input, $output);
        return $output->fetch();
    }


    public function makeMigrateDir($path = 'db/migrations')
    {
        $basePath = $this->artisan->basePath();


        $dir = $basePath . DIRECTORY_SEPARATOR . $path;


        $files = $this->artisan['files'];

        //检查目录是否被创建,如果目录不存在就直接创建
        if (!$files->isDirectory($dir)) {
            $files->makeDirectory($dir, 0777, true, true);
        }
    }


}
