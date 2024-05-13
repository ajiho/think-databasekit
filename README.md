## think-databasekit

| **illuminate/database** | **think-databasekit** |
|-------------------------|---------------------|
| ^7.0                     | ^1.0                |



喜欢tp6的目录结构和框架的轻量和灵活？又想使用laravel的orm操作数据库？

`think-databasekit`是[thinkphp6.0+](https://github.com/top-think/framework)
和[illuminate/database](https://github.com/illuminate/database)的粘合剂，它可以让你光速体验
laravel的orm操作。


## 特性

- 无侵入性安装，不和tp官方有任何冲突
- 不影响tp内置的orm功能
- 提供和`artisan`一模一样的指令,让你快速创建所需文件基本模板
- 不单单是orm,也是一个强大的数据库迁移工具


## 安装

```
composer require ajiho/think-databasekit
```


安装过程中会询问你是否确认安装该插件？ `y`

安装完毕后运行`php think`可以看到新增指令

~~~
 dbk
  dbk:db:seed           Seed the database with records
  dbk:db:wipe           Drop all tables, views, and types
  dbk:make:factory      Create a new model factory
  dbk:make:migration    Create a new migration file
  dbk:make:model        Create a new Eloquent model class
  dbk:make:seeder       Create a new seeder class
  dbk:migrate           Run the database migrations
  dbk:migrate:fresh     Drop all tables and re-run all migrations
  dbk:migrate:install   Create the migration repository
  dbk:migrate:refresh   Reset and re-run all migrations
  dbk:migrate:reset     Rollback all database migrations
  dbk:migrate:rollback  Rollback all database migrations
  dbk:migrate:status    Show the status of each migration
~~~

## 配置

安装完成后会同时在config目录下生`databasekit.php`

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

```
[DB]
HOST = 127.0.0.1
PORT = 3306
DATABASE = test
USERNAME = username
PASSWORD = password
CHARSET =  utf8
COLLATION = utf8_general_ci
PREFIX =  test_
```



## 文档地址

- https://laravel.com/docs/7.x/migrations
- https://laravel.com/docs/7.x/eloquent
- https://laravel.com/docs/7.x/database


## 计划


可以看到目前提供的文档地址是7.x的版本，所以目前的计划就是，先在一个项目里实战后，看看哪里还有什么问题
以及需要优化的地方、再陆续升级到8.x、9.x、10.x



## 补充说明

以下是几个重要的补充，请认真阅读，如果我在实战过程中，还有后续问题，会继续补充。


### 模型的操作

如果您是第一次使用`illuminate/database`的模型，它不像thinkphp框架一样，创建就能操作了,您需要注意几件事情。



1.laravel的表名是复数形式问题，因此如果您的数据表没有保持复数形式，您可能
需要去模型指定表名

```php
declare (strict_types = 1);

namespace app\common\model;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';

}
```

2.您可能已经习惯性的使用类似`create()`这种批量赋值新增的方法。

在tp框架中，您可能是直接第二个参数加一个true就可以直接过滤非数据库字段就插入了。

```php
$admin = \app\common\model\Admin::create($params, true);
```

那么您在`illuminate/database`模型中，则需要指定模型的黑名单，或者白名单,因此建议您可以新建一个Base模型
，其它模型都继承它

```php
php think dbk:make:model Base

//多应用模式
php think dbk:make:model common@Base
```


```php
declare (strict_types = 1);

namespace app\common\model;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;


class Base extends Model
{

    
    protected $dateFormat = 'U';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


    // 设置添加时的黑名单
    protected $guarded = [];


    // 处理时间格式化问题
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
```

这种才可以正常的使用create方法新增数据




### 分页

`illuminate/database`该库本身是单纯
的提供数据库支持的，所以你按照laravel的文档按照如下方式调用快捷分页方法时,它会报错`Class 'Illuminate\Pagination\Paginator' not found`

```php
$admins = \app\common\model\Admin::where('status', 1)->orderBy('id', 'desc')->paginate(10);
```


这不是`think-databasekit`封装导致它无法使用的 ,因为如果你要使用它的原本的分页方法你还必须安装

- [illuminate/pagination](https://packagist.org/packages/illuminate/pagination)
- [illuminate/view](https://packagist.org/packages/illuminate/view)
- ....

为什么还有一个省略号？因为我在实际测试中，发现它最后会依赖其它的`illuminate/*`系列组件

本身`illuminate/database`的初衷就只是为了在laravel框架之外使用这个优雅而强大的数据库操作,因此
我一开始的强行支持`paginate`方法的正常使用就是错的，因为安装了太多原本不需要的东西。

那么就没有办法优雅的使用`paginate`方法来进行快速分页了么？

不用担心,`think-databasekit`已经帮您做好了这一切。


```php
// 分页
$admins = \app\common\model\Admin::where('status', 1)->orderBy('id', 'desc')->dbkPaginate(2);

//简单分页
$admins = \app\common\model\Admin::where('status', 1)->orderBy('id', 'desc')->dbkSimplePaginate(2);
```

`dbkPaginate`、和`dbkSimplePaginate`方法底层使用的tp框架的分页驱动，因此在`illuminate/database`原本的`paginate()`方法上追加了3个参数,

以下是具体参数和默认值
```
dbkPaginate(
$perPage = null,  //每页数量-》类似tp的分页参数中的list_rows
$columns = ['*'], 
$pageName = 'page', //分页变量-》类似tp的分页参数中的var_page
$page = null     // 当前页-》类似tp的分页参数中的page(这里刚好名字相同)
$query = [], //追加的参数-》   url额外参数
$fragment = '',//追加的参数-》 url锚点
$path = null //追加的参数--》   url路径
)
```

具体可以查看tp的官方文档的[分页参数部分](https://www.kancloud.cn/manual/thinkphp6_0/1037638)因此您想追加分页参数，应该就很容易明白了。以及在视图文件中渲染，也是按照原本tp的分页去渲染就完事了。


