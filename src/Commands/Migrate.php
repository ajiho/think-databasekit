<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Migrate extends Command
{
    /**
     * migrate {--database= : The database connection to use}
     * {--force : Force the operation to run when in production}
     * {--path=* : The path(s) to the migrations files to be executed}
     * {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
     * {--pretend : Dump the SQL queries that would be run}
     * {--seed : Indicates if the seed task should be re-run}
     * {--step : Force the migrations to be run so they can be rolled back individually}'
     * @return void
     */
    protected function configure()
    {
        $this->setName('idb:migrate')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('force', null, Option::VALUE_OPTIONAL, 'Force the operation to run when in production')
            ->addOption('path', null, Option::VALUE_OPTIONAL, 'The path(s) to the migrations files to be executed')
            ->addOption('realpath', null, Option::VALUE_OPTIONAL, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('pretend', null, Option::VALUE_OPTIONAL, 'Dump the SQL queries that would be run')
            ->addOption('seed', null, Option::VALUE_OPTIONAL, 'Indicates if the seed task should be re-run')
            ->addOption('step', null, Option::VALUE_OPTIONAL, 'Force the migrations to be run so they can be rolled back individually')
            ->setDescription('Run the database migrations');
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

        if ($input->hasOption('force')) {
            $arguments += ['--force'];
        }

        if ($input->hasOption('realpath')) {
            $arguments += ['--realpath'];
        }

        if ($input->hasOption('pretend')) {
            $arguments += ['--pretend'];
        }

        if ($input->hasOption('seed')) {
            $arguments += ['--seed'];
        }

        if ($input->hasOption('step')) {
            $arguments += ['--step'];
        }

        return $this->laravel->runCommand('migrate', $arguments, true);


    }

}
