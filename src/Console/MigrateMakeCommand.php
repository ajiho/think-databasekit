<?php

namespace ajiho\IlluminateDatabase\Console;

use Illuminate\Support\Facades\Facade;

class MigrateMakeCommand extends \Illuminate\Database\Console\Migrations\MigrateMakeCommand
{
    protected function getMigrationPath()
    {
        $path = parent::getMigrationPath();


        $app = Facade::getFacadeApplication();

        $files = $app['files'];

        if (!$files->isDirectory($path)) {
            $files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }


}
