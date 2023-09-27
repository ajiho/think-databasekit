<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Fresh extends Command
{

    protected function configure()
    {
        $this->setName('idb:migrate:fresh')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('drop-views', null, Option::VALUE_NONE, 'Drop all tables and views')
            ->addOption('drop-types', null, Option::VALUE_NONE, 'Drop all tables and types (Postgres only)')
            ->addOption('force', null, Option::VALUE_NONE, 'Force the operation to run when in production')
            ->addOption('path', null, Option::VALUE_OPTIONAL|Option::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed')
            ->addOption('realpath', null, Option::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('seed', null, Option::VALUE_NONE, 'Indicates if the seed task should be re-run')
            ->addOption('seeder', null, Option::VALUE_OPTIONAL, 'The class name of the root seeder')
            ->addOption('step', null, Option::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually')
            ->setDescription('Drop all tables and re-run all migrations');
    }

    protected function execute(Input $input, Output $output)
    {

        //参数准备
        $arguments = [];
        if ($input->hasOption('database')) {
            $arguments += ['--database' => $input->getOption('database')];
        }

        if ($input->hasOption('drop-views')) {
            $arguments += ['--drop-views'];
        }

        if ($input->hasOption('drop-types')) {
            $arguments += ['--drop-types'];
        }
        if ($input->hasOption('force')) {
            $arguments += ['--force'];
        }

        if ($input->hasOption('path')) {
            $arguments += ['--path' => $input->getOption('path')];
        }

        if ($input->hasOption('realpath')) {
            $arguments += ['--realpath'];
        }

        if ($input->hasOption('seed')) {
            $arguments += ['--seed'];
        }

        if ($input->hasOption('seeder')) {
            $arguments += ['--seeder' => $input->getOption('seeder')];
        }

        if ($input->hasOption('step')) {
            $arguments += ['--step'];
        }

        return $this->laravel->run('migrate:fresh', $arguments, true);

    }

}
