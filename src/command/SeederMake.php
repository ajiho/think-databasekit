<?php

namespace ajiho\databasekit\command;

use ajiho\databasekit\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class SeederMake extends Command
{
    protected function configure()
    {
        $this->setName('dbk:make:seeder')
            ->addArgument('name', Argument::OPTIONAL, 'The name of the class')
            ->setDescription('Create a new seeder class');
    }

    protected function execute(Input $input, Output $output)
    {

        $name = trim($input->getArgument('name'));
        //参数准备
        $arguments = ['name' => $name];

        $this->call('make:seeder', $arguments);

    }
}
