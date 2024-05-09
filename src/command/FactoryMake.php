<?php

namespace ajiho\databasekit\command;

use ajiho\databasekit\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class FactoryMake extends Command
{

    protected function configure()
    {
        $this->setName('dbk:make:factory')
            ->addArgument('name', Argument::OPTIONAL, 'The name of the class')
            ->addOption('model', 'm', Option::VALUE_OPTIONAL, 'The name of the model')
            ->setDescription('Create a new model factory');
    }

    protected function execute(Input $input, Output $output)
    {



        $name = trim($input->getArgument('name'));

        //参数准备
        $arguments = ['name' => $name];
        if ($input->hasOption('model')) {
            $arguments += ['--model' => $input->getOption('model')];
        }



       $this->call('make:factory', $arguments);

    }
}
