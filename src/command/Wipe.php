<?php

namespace ajiho\databasekit\command;

use ajiho\databasekit\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Wipe extends Command
{
    protected function configure()
    {
        $this->setName('dbk:db:wipe')
            ->addOption('database', null,Option::VALUE_OPTIONAL, 'The database connection to use')
            ->addOption('drop-views', null,Option::VALUE_NONE, 'Drop all tables and views')
            ->addOption('drop-types', null,Option::VALUE_NONE, 'Drop all tables and types (Postgres only)')
            ->addOption('force', null,Option::VALUE_NONE, 'Force the operation to run when in production')
            ->setDescription('Drop all tables, views, and types');
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

        $this->call('db:wipe', $arguments);

    }

}
