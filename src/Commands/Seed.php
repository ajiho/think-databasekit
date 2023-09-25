<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Seed extends Command
{

    protected function configure()
    {
        $this->setName('idb:db:seed')
            ->addOption('class', null, Option::VALUE_OPTIONAL, 'The class name of the root seeder','DatabaseSeeder')
            ->addOption('database', null, Option::VALUE_OPTIONAL, 'The database connection to seed')
            ->addOption('force', null, Option::VALUE_NONE, 'Force the operation to run when in production')
            ->setDescription('Seed the database with records');
    }

    protected function execute(Input $input, Output $output)
    {

        //参数准备
        $arguments = [];
        if ($input->hasOption('class')) {
            $arguments += ['--class' => $input->getOption('class')];
        }

        if ($input->hasOption('database')) {
            $arguments += ['--database' => $input->getOption('database')];
        }

        if ($input->hasOption('force')) {
            $arguments += ['--force'];
        }

        return $this->laravel->runCommand('db:seed', $arguments, true);

    }
}
