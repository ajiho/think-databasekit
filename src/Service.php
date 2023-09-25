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
        //实例化容器
        new Application(root_path());

        //注册帮助指令
        $this->commands([
            \ajiho\IlluminateDatabase\Commands\MigrateMake::class,
            \ajiho\IlluminateDatabase\Commands\ModelMake::class,
            \ajiho\IlluminateDatabase\Commands\Migrate::class,
            \ajiho\IlluminateDatabase\Commands\Seed::class,
            \ajiho\IlluminateDatabase\Commands\SeederMake::class,
        ]);
    }


}
