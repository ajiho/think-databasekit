<?php

namespace ajiho\databasekit\command;

use ajiho\databasekit\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Rollback extends Command
{
    protected function configure()
    {
        $this->setName('dbk:migrate:rollback')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('force', null, Option::VALUE_NONE, 'Force the operation to run when in production')
            ->addOption('path', null, Option::VALUE_OPTIONAL|Option::VALUE_IS_ARRAY, 'The path(s) to the migrations files to be executed')
            ->addOption('realpath', null, Option::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('pretend', null, Option::VALUE_NONE, 'Dump the SQL queries that would be run')
            ->addOption('step', null, Option::VALUE_OPTIONAL, 'The number of migrations to be reverted')
            ->setDescription('Rollback all database migrations');
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

        if ($input->hasOption('pretend')) {
            $arguments += ['--pretend'];
        }

        if ($input->hasOption('step')) {
            $arguments += ['--step' => $input->getOption('step')];
        }

        $this->call('migrate:rollback', $arguments);

    }
}
