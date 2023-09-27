<?php

namespace ajiho\IlluminateDatabase\Commands;

use ajiho\IlluminateDatabase\Command;
use Symfony\Component\Console\Input\ArrayInput;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;


class ModelMake extends Command
{

    private $type = 'model';

    protected function configure()
    {

        $this->setName('idb:make:model')
            ->addArgument('name', Argument::REQUIRED, "The name of the class")
            ->setDescription('Create a new Eloquent model class');
    }


    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));
        $classname = $this->getClassName($name);
        $pathname = $this->getPathName($classname);


        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ':' . $classname . ' already exists!</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $this->buildClass($classname));
        $output->writeln('<info>' . $this->type . ':' . $classname . ' created successfully.</info>');

    }


    protected function getPathName(string $name): string
    {
        //把前面的'app\'给去掉
        $name = substr($name, 4);
        return $this->app->getBasePath() . ltrim(str_replace('\\', '/', $name), '/') . '.php';
    }


    protected function getClassName(string $name): string
    {


        if (strpos($name, '\\') !== false) {
            return $name;
        }


        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }


        if (strpos($name, '/') !== false) {
            $name = str_replace('/', '\\', $name);
        }


        return 'app' . ($app ? '\\' . $app : '') . '\\model' . '\\' . $name;
    }


    protected function buildClass(string $name): string
    {
        $stub = file_get_contents($this->subsPath . 'model.stub');
        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
        $class = str_replace($namespace . '\\', '', $name);
        return str_replace(['{%className%}', '{%namespace%}'], [
            $class,
            $namespace,
        ], $stub);
    }
}
