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
        'mysql2' => [
            'driver' => 'mysql',
            'url' => env('database.url'),
            'host' => env('db.host', '127.0.0.1'),
            'port' => env('db.port', '3306'),
            'database' => 'phinx2',
            'username' => env('db.username', 'root'),
            'password' => env('db.password', ''),
            'unix_socket' => env('db.socket', ''),
            'charset' => env('db.charset', 'utf8mb4'),
            'collation' => env('db.collation', 'utf8mb4_unicode_ci'),
            'prefix' => 'px2_',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]
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