<?php

namespace ajiho\databasekit\console;


class MigrateMakeCommand extends \Illuminate\Database\Console\Migrations\MigrateMakeCommand
{
    protected function getMigrationPath()
    {
        $path = parent::getMigrationPath();
        if (!$this->laravel['files']->isDirectory($path)) {
            $this->laravel['files']->makeDirectory($path, 0777, true, true);
        }
        return $path;
    }


}
