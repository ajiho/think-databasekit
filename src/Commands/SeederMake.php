<?php

namespace ajiho\IlluminateDatabase\Commands;

use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class SeederMake extends \think\console\Command
{
    protected function configure()
    {
        $this->setName('phinx:create')
            ->addArgument('name', Argument::OPTIONAL, 'Class name of the migration (in CamelCase)')->setHelp(sprintf(
                '%sCreates a new database migration%s',
                PHP_EOL,
                PHP_EOL
            ))
            ->addOption('table', null, Option::VALUE_OPTIONAL, 'Table name of the data table','')
            ->setDescription('Run the phinx [Create] command');
    }

    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        $tableName = $input->getOption('table');

        if (empty($name)) {
            $this->output->error('The name of the migrate file cannot be empty. Please use CamelCase format.');
            return;
        }
        $result = $this->phinxCreateCommand($name,$this->migrate_stub_path);
        //处理表名
        $this->setTableName($result,$tableName);
        $output->writeln($result);

    }
}
