<?php

namespace ajiho\IlluminateDatabase;

use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

class Service extends \think\Service
{


    protected $config;

    public function register()
    {
        $this->connect();

        $this->commands([
            \ajiho\IlluminateDatabase\Commands\MigrateMake::class,
            \ajiho\IlluminateDatabase\Commands\ModelMake::class,
        ]);
    }

    public function connect()
    {
        $this->config = config('illuminate-database');

        //获取连接信息
        $connections = isset($this->config['connections']) && is_array($this->config['connections']) ? $this->config['connections'] : [];

        $capsule = new Capsule;

        //添加连接
        foreach ($connections as $connectionName => $connection) {
            $capsule->addConnection($connection, $connectionName);
        }

        //获取默认的数据库连接
        $default = isset($app->config['default']) && is_string($app->config['default']) ? $app->config['default'] : 'default';

        //设置默认数据库连接
        $capsule->getDatabaseManager()->setDefaultConnection($default);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $this->app->bind('capsule', $capsule);


    }

}
