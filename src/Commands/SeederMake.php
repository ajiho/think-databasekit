<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class SeederMake extends Command
{
    protected function configure()
    {
        $this->setName('idb:make:seeder')
            ->addArgument('name', Argument::OPTIONAL, 'The name of the class')
            ->setDescription('Create a new seeder class');
    }

    protected function execute(Input $input, Output $output)
    {

        $name = trim($input->getArgument('name'));

        //参数准备
        $arguments = ['name' => $name];

        return $this->laravel->runCommand('make:seeder', $arguments, true);

    }
}
