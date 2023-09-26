## think-illuminate-database

喜欢tp6的目录结构和框架的轻量和灵活？又想使用laravel的orm操作数据库？


让thinkphp6的轻量+laravel的orm=数据库迁移工具+强大的数据库操作+愉悦的开发心情

## 特性

- 无侵入性安装，不和tp官方有任何冲突
- 不影响tp内置的orm功能
- 集成和artisan一模一样的指令,让你无缝过渡
- 不单单是orm,也是一个强大的数据库迁移工具


## 安装


```
composer require ajiho/think-illuminate-database
```

安装过程中会询问你是否确认安装该插件？ `y`


安装完毕后运行`php think`可以看到新增指令

~~~
 idb
  idb:db:seed           Seed the database with records
  idb:db:wipe           Drop all tables, views, and types
  idb:make:factory      Create a new model factory
  idb:make:migration    Create a new migration file
  idb:make:model        Create a new laravel model class
  idb:make:seeder       Create a new seeder class
  idb:migrate           Run the database migrations
  idb:migrate:fresh     Drop all tables and re-run all migrations
  idb:migrate:install   Create the migration repository
  idb:migrate:refresh   Reset and re-run all migrations
  idb:migrate:reset     Rollback all database migrations
  idb:migrate:rollback  Rollback all database migrations
  idb:migrate:status    Show the status of each migration
~~~

同时在config目录下生成配置文件`illuminate-database.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 默认数据库连接名称
    |--------------------------------------------------------------------------
    |
    |在这里，您可以指定下面的数据库连接
    |用作所有数据库工作的默认连接。当然
    |您可以使用数据库库同时使用多个连接。
    |
    */

    'default' => env('db.connection', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | 数据库连接
    |--------------------------------------------------------------------------
    |
    |以下是为您的应用程序设置的每个数据库连接。
    |当然，配置每个数据库平台的示例
    |下面显示了由Laravel支持的，以简化开发。
    |
    |
    |Laravel中的所有数据库工作都是通过PHP PDO设施完成的
    |因此，请确保您拥有的特定数据库的驱动程序
    |在您开始开发之前，选择已安装在您的机器上。
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('db.url'),
            'host' => env('db.host', '127.0.0.1'),
            'port' => env('db.port', '3306'),
            'database' => env('db.database', ''),
            'username' => env('db.username', 'root'),
            'password' => env('db.password', ''),
            'unix_socket' => env('db.socket', ''),
            'charset' => env('db.charset', 'utf8mb4'),
            'collation' => env('db.collation', 'utf8mb4_unicode_ci'),
            'prefix' => env('db.prefix', ''),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ],
        // 更多的数据库配置信息
    ],

    /*
    |--------------------------------------------------------------------------
    | 迁移存储库表
    |--------------------------------------------------------------------------
    |
    |此表跟踪已运行的所有迁移
    |您的申请。使用这些信息，我们可以确定
    |磁盘上的迁移实际上并没有在数据库中运行。
    |
    */

    'migrations' => 'migrations',


    /*
    |--------------------------------------------------------------------------
    | Faker本地化
    |--------------------------------------------------------------------------
    |
    |Faker PHP库在生成fake时将使用此区域设置
    |数据库种子的数据。例如，这将用于
    |本地电话号码、街道地址信息等。
    |
    */

    'faker_locale' => 'zh_CN',

];

```

此时你可以把下面的代码复制到您的`.env`文件中,快速配置后即可开始享受这一切！

```php
[DB]
HOST = 127.0.0.1
PORT = 3306
DATABASE = test
USERNAME = username
PASSWORD = password
CHARSET =  utf8mb4
```



## 文档地址

https://laravel.com/docs/7.x/eloquent
https://laravel.com/docs/7.x/database
