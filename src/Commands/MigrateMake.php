<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use Symfony\Component\Console\Input\ArrayInput;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;


class MigrateMake extends Command
{

    /**
     * Create a new migration file
     * 'make:migration {name : The name of the migration}
     * {--create= : The table to be created}
     * {--table= : The table to migrate}
     * {--path= : The location where the migration file should be created}
     * {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
     * {--fullpath : Output the full path of the migration}';
     * @return void
     */
    protected function configure()
    {
        $this->setName('idb:make:migration')
            ->addArgument('name', Argument::OPTIONAL, 'The name of the migration')
            ->addOption('create', null, Option::VALUE_OPTIONAL, 'The table to be created')
            ->addOption('table', null, Option::VALUE_OPTIONAL, 'The table to migrate')
            ->addOption('path', null, Option::VALUE_OPTIONAL, 'The location where the migration file should be created')
            ->addOption('realpath', null, Option::VALUE_OPTIONAL, 'Indicate any provided migration file paths are pre-resolved absolute paths')
            ->addOption('fullpath', null, Option::VALUE_OPTIONAL, 'Output the full path of the migration')
            ->setDescription('Create a new migration file');
    }

    protected function execute(Input $input, Output $output)
    {
        //迁移文件名
        $name = trim($input->getArgument('name'));

        $path = 'db/migrations';
        //创建迁移文件目录
        $this->artisan->makeMigrateDir($path);


        //找到迁移指令
        $command = $this->artisan['artisan']->find('make:migration');


        //参数准备
        $arguments = ['name' => $name];
        if ($input->hasOption('create')) {
            $arguments += ['--create' => $input->getOption('create')];
        }

        if ($input->hasOption('table')) {
            $arguments += ['--table' => $input->getOption('table')];
        }

        $arguments += ['--path' => $input->hasOption('path') ? $input->getOption('path') : $path];

        if ($input->hasOption('realpath')) {
            $arguments += ['--realpath'];
        }

        if ($input->hasOption('fullpath')) {
            $arguments += ['--fullpath'];
        }

        //执行命令
        $result = $this->artisan->runCommand($command, $arguments);


        //输出
        $output->writeln($result);

    }
}
