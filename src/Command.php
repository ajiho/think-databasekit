<?php

namespace ajiho\databasekit;


use Symfony\Component\Console\Output\ConsoleOutput;

abstract class Command extends \think\console\Command
{

    public function getApplication()
    {

        return new Application($this->app,$this->app->getRootPath());
    }


    public function call($command,array $parameters = [])
    {

        $app = $this->getApplication();


        $app['app.console']->call($command, $parameters, new ConsoleOutput());
    }

}
