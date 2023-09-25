<?php

namespace ajiho\IlluminateDatabase;


use Illuminate\Support\Facades\Facade;

class Command extends \think\console\Command
{


    protected $laravel;

    protected $subsPath;

    public function __construct()
    {

        parent::__construct();

        $app = Facade::getFacadeApplication();

        $app->registerCommands();

        $this->laravel = $app;

        $this->subsPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

    }


}
