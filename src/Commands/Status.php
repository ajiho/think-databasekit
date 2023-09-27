<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Status extends Command
{
    protected function configure()
    {
        $this->setName('idb:migrate:status')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('path', null, Option::VALUE_OPTIONAL | Option::VALUE_IS_ARRAY, 'The path(s) to the migrations files to use')
            ->addOption('realpath', null, Option::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->setDescription('Show the status of each migration');
    }

    protected function execute(Input $input, Output $output)
    {

        //参数准备
        $arguments = [];
        if ($input->hasOption('database')) {
            $arguments += ['--database' => $input->getOption('database')];
        }


        if ($input->hasOption('path')) {
            $arguments += ['--path' => $input->getOption('path')];
        }

        if ($input->hasOption('realpath')) {
            $arguments += ['--realpath'];
        }


        return $this->laravel->run('migrate:status', $arguments, true);
    }
}
