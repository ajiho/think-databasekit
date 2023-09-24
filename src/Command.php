<?php

namespace ajiho\IlluminateDatabase;


class Command extends \think\console\Command
{


    protected $artisan;

    public function __construct()
    {

        parent::__construct();

        //在这里应该实例化artisan,并且应该传入当前think容器中的idb数据库管理器,提高性能
        $this->artisan = new Application();

    }


}
