<?php

namespace ajiho\IlluminateDatabase\Console;




class SeederMakeCommand extends \Illuminate\Database\Console\Seeds\SeederMakeCommand
{
    /**
     * 返回seeders的存根目录
     *
     * @return string
     */
    protected function getStub()
    {
        return root_path() . 'app/stubs/seeder.stub';
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
