<?php

namespace ajiho\databasekit\command;

use ajiho\databasekit\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;


class MigrateMake extends Command
{

    protected function configure()
    {
        $this->setName('dbk:make:migration')
            ->addArgument('name', Argument::OPTIONAL, 'The name of the migration')
            ->addOption('create', null, Option::VALUE_OPTIONAL, 'The table to be created')
            ->addOption('table', null, Option::VALUE_OPTIONAL, 'The table to migrate')
            ->addOption('path', null, Option::VALUE_OPTIONAL, 'The location where the migration file should be created')
            ->addOption('realpath', null, Option::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('fullpath', null, Option::VALUE_NONE, 'Output the full path of the migration')
            ->setDescription('Create a new migration file');
    }

    protected function execute(Input $input, Output $output)
    {

        $name = trim($input->getArgument('name'));

        //参数准备
        $arguments = ['name' => $name];
        if ($input->hasOption('create')) {
            $arguments += ['--create' => $input->getOption('create')];
        }

        if ($input->hasOption('table')) {
            $arguments += ['--table' => $input->getOption('table')];
        }

        if ($input->hasOption('path')) {
            $arguments += ['--path' => $input->getOption('path')];
        }

        if ($input->hasOption('realpath')) {
            $arguments += ['--realpath'];
        }

        if ($input->hasOption('fullpath')) {
            $arguments += ['--fullpath'];
        }



        $this->call('make:migration', $arguments);

    }
}
