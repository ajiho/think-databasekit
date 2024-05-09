<?php

namespace ajiho\databasekit\console;

class SeedCommand extends \Illuminate\Database\Console\Seeds\SeedCommand
{
    protected function getDatabase()
    {
        $database = $this->input->getOption('database');
        return $database ?: $this->laravel['app.think']->config->get('databasekit.default');
    }


}
