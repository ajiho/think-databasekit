<?php

namespace ajiho\IlluminateDatabase\Console;

class FactoryMakeCommand extends \Illuminate\Database\Console\Factories\FactoryMakeCommand
{
    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function userProviderModel()
    {

        return 'app\model\User';
    }


}
