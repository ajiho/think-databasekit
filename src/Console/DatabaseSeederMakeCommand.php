<?php

namespace ajiho\IlluminateDatabase\Console;

class DatabaseSeederMakeCommand extends \Illuminate\Database\Console\Seeds\SeederMakeCommand
{

    protected $name = 'make:databaseseeder';

    /**
     * 返回seeders的存根目录
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->laravel['path.stubs'] . DIRECTORY_SEPARATOR.'databaseSeeder.stub';
    }


    /**
     * 获取默认防护的用户提供程序的模型(重写此方法,避免引入多余的illuminate/config库)
     *
     * @return string|null
     */
    protected function userProviderModel()
    {
        return 'app\model\User';
    }

}
