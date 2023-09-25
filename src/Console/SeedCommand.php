<?php

namespace ajiho\IlluminateDatabase\Console;

class SeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');
        return $database ?: $this->laravel['config.idb']['default'];
    }


}
