<?php

namespace ajiho\databasekit;


use ajiho\databasekit\command\FactoryMake;
use ajiho\databasekit\command\Fresh;
use ajiho\databasekit\command\Install;
use ajiho\databasekit\command\Migrate;
use ajiho\databasekit\command\MigrateMake;
use ajiho\databasekit\command\ModelMake;
use ajiho\databasekit\command\Refresh;
use ajiho\databasekit\command\Reset;
use ajiho\databasekit\command\Rollback;
use ajiho\databasekit\command\Seed;
use ajiho\databasekit\command\SeederMake;
use ajiho\databasekit\command\Status;
use ajiho\databasekit\command\Wipe;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use think\Paginator;
use think\Collection;


class Service extends \think\Service
{

    public function register()
    {
        if ($this->app->config->has('databasekit')) {

            $capsule = new Capsule;

            $default = $this->app->config->get('databasekit.default');

            $connections = $this->app->config->get('databasekit.connections');

            //多库连接
            foreach ($connections as $connectionName => $connection) {
                $capsule->addConnection($connection, $connectionName);
            }

            //设置默认数据库连接
            $capsule->getDatabaseManager()->setDefaultConnection($default);

            //设置Eloquent模型使用的事件调度程序
            $capsule->setEventDispatcher(new Dispatcher(new Container()));

            //通过静态方法使此Capsule实例全局可用
            $capsule->setAsGlobal();

            //设置Eloquent ORM(需要设置setEventDispatcher可用)
            $capsule->bootEloquent();

            //绑定到容器中
            $this->app->bind('capsule', $capsule);
        }

    }

    public function boot()
    {

        $this->macro();

        //注册帮助指令
        $this->commands([
            FactoryMake::class,
            Fresh::class,
            Install::class,
            Migrate::class,
            MigrateMake::class,
            ModelMake::class,
            Refresh::class,
            Reset::class,
            Rollback::class,
            Seed::class,
            SeederMake::class,
            Status::class,
            Wipe::class,
        ]);
    }


    public function macro()
    {

        //重写DB类的simplePaginate,底层使用tp6的分页驱动
        Builder::macro('dbkSimplePaginate', function (
            $perPage = 15,
            $columns = ['*'],
            $pageName = 'page',
            $page = null,
            $query = [],
            $fragment = '',
            $path = null
        ) {

            $page = $page ?: Paginator::getCurrentPage($pageName);
            $this->offset(($page - 1) * $perPage)->limit($perPage + 1);

            $results = new Collection([]);
            $collections = $this->get($columns);
            $collections->map(function ($item) use ($results) {
                $results->push($item);
            });


            $config = [
                'query' => $query, //url额外参数
                'fragment' => $fragment, //url锚点
                'path' => $path ?: Paginator::getCurrentPath(), //url路径
                'var_page' => $pageName, //分页变量
                'list_rows' => $perPage, //每页数量
            ];

            return Paginator::make($results, $perPage, $page, null, true, $config);

        });


        //重写模型simplePaginate简单分页方法,让底层使用tp6的分页驱动
        EloquentBuilder::macro('dbkSimplePaginate', function (
            $perPage = null,
            $columns = ['*'],
            $pageName = 'page',
            $page = null,
            $query = [],
            $fragment = '',
            $path = null
        ) {

            $page = $page ?: Paginator::getCurrentPage($pageName);


            $perPage = $perPage ?: $this->model->getPerPage();
            //接下来，我们将设置此查询的限制和偏移量，以便在获得
            //结果我们得到了结果的适当部分。然后，我们将创建完整的
            //给定页面和每页的这些结果的分页器实例。
            $this->skip(($page - 1) * $perPage)->take($perPage + 1);

            $results = new Collection([]);
            $collections = $this->get($columns);
            $collections->map(function ($item) use ($results) {
                $results->push($item);
            });

            $config = [
                'query' => $query, //url额外参数
                'fragment' => $fragment, //url锚点
                'path' => $path ?: Paginator::getCurrentPath(), //url路径
                'var_page' => $pageName, //分页变量
                'list_rows' => $perPage, //每页数量
            ];

            return Paginator::make($results, $perPage, $page, null, true, $config);

        });

        //重写DB类的paginate,底层使用tp6的分页驱动
        Builder::macro('dbkPaginate', function (
            $perPage = 15,
            $columns = ['*'],
            $pageName = 'page',
            $page = null,
            $query = [],
            $fragment = '',
            $path = null
        ) {
            $page = $page ?: Paginator::getCurrentPage($pageName);
            $total = $this->getCountForPagination();

            $results = new Collection([]);
            $collections = $total ? $this->forPage($page, $perPage)->get($columns) : \Illuminate\Support\collect();
            $collections->map(function ($item) use ($results) {
                $results->push($item);
            });

            $config = [
                'query' => $query, //url额外参数
                'fragment' => $fragment, //url锚点
                'path' => $path ?: Paginator::getCurrentPath(), //url路径
                'var_page' => $pageName, //分页变量
                'list_rows' => $perPage, //每页数量
            ];
            return Paginator::make($results, $perPage, $page, $total, false, $config);
        });


        //重写模型的paginate,使用tp6的分页驱动
        EloquentBuilder::macro('dbkPaginate', function (
            $perPage = null,
            $columns = ['*'],
            $pageName = 'page',
            $page = null,
            $query = [],
            $fragment = '',
            $path = null
        ) {

            $results = new Collection([]);
            $page = $page ?: Paginator::getCurrentPage($pageName);
            $perPage = $perPage ?: $this->model->getPerPage();
            $total = $this->toBase()->getCountForPagination();
            $collections = ($total) ? $this->forPage($page, $perPage)->get($columns) : $this->model->newCollection();
            $collections->map(function ($item) use ($results) {
                $results->push($item);
            });


            $config = [
                'query' => $query, //url额外参数
                'fragment' => $fragment, //url锚点
                'path' => $path ?: Paginator::getCurrentPath(), //url路径
                'var_page' => $pageName, //分页变量
                'list_rows' => $perPage, //每页数量
            ];
            return Paginator::make($results, $perPage, $page, $total, false, $config);
        });

    }


}
