<?php

namespace ajiho\IlluminateDatabase\Console;




class SeederMakeCommand extends \Illuminate\Database\Console\Seeds\SeederMakeCommand
{
    /**
     * 返回seeders的存根目录
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->laravel['path.stubs'] . DIRECTORY_SEPARATOR.'seeder.stub';
    }


    /**
     * 获取默认防护的用户提供程序的模型(重写此方法,避免引入多余的illuminate/config库)
     *
     * @return string|null
     */
    protected function userProviderModel()
    {
        return 'app\model\User';
    }

    /**
     * 重写此方法用于每次创建种子文件都自动创建DatabaseSeeder种子调度中心
     * @param $name
     * @return string
     */
    protected function getPath($name)
    {

        $path = parent::getPath($name);

        //先判断目录是否存在
        $files = $this->laravel['files'];

        //先判断文件是否存在
        $databaseSeederPath = dirname($path) . DIRECTORY_SEPARATOR . 'DatabaseSeeder' . '.php';

        $exists = $files->exists($databaseSeederPath);

        if (!$exists) {

            $files->makeDirectory(dirname($databaseSeederPath), 0777, true, true);
            //找到存根

            $stub = file_get_contents($this->laravel['path.stubs'] . DIRECTORY_SEPARATOR . 'databaseSeeder.stub');

            $newStub = str_replace(['{{ class }}'], [
                'DatabaseSeeder',
            ], $stub);

            //写入原本的路径
            file_put_contents($databaseSeederPath, $newStub);
        }

        return $path;
    }

}
