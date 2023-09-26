<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Refresh extends Command
{
    protected function configure()
    {
        $this->setName('idb:migrate:refresh')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('force', null, Option::VALUE_NONE, 'Force the operation to run when in production')
            ->addOption('path', null, Option::VALUE_OPTIONAL|Option::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed')
            ->addOption('realpath', null, Option::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('seed', null, Option::VALUE_NONE, 'Indicates if the seed task should be re-run')
            ->addOption('seeder', null, Option::VALUE_OPTIONAL, 'The class name of the root seeder')
            ->addOption('step', null, Option::VALUE_OPTIONAL, 'The number of migrations to be reverted & re-run')
            ->setDescription('Reset and re-run all migrations');
    }

    protected function execute(Input $input, Output $output)
    {

        //参数准备
        $arguments = [];
        if ($input->hasOption('database')) {
            $arguments += ['--database' => $input->getOption('database')];
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
            $arguments += ['--step' => $input->getOption('step')];
        }

        return $this->laravel->runCommand('migrate:refresh', $arguments, true);

    }
}
