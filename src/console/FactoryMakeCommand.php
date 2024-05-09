<?php

namespace ajiho\databasekit\console;

class FactoryMakeCommand extends \Illuminate\Database\Console\Factories\FactoryMakeCommand
{

    protected function getStub()
    {

        return $this->laravel['path.stubs.factory'];

    }

    /**
     * 获取默认保护的用户提供程序的模型。
     *
     * @return string|null
     */
    protected function userProviderModel()
    {

        return 'app\model\User';
    }






}
