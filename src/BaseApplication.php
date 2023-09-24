<?php

namespace ajiho\IlluminateDatabase;

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use RuntimeException;

class BaseApplication extends Container implements ApplicationContract
{

    const VERSION = '7';

    /**
     * 应用的基本路径
     * @var string
     */
    protected $basePath;
    protected $appPath;

    /**
     * 开发人员定义的自定义数据库路径。
     *
     * @var string
     */
    protected $databasePath;


    /**
     * @var int|string
     */
    protected $namespace;


    public function __construct()
    {
        $this->basePath = rtrim(root_path(), '\/');
    }

    /**
     * 获取应用程序的版本号。
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * 获取Laravel安装的基本路径。
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 获取引导程序目录的路径。
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取应用程序配置文件的路径
     *
     * @param string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取数据库目录的路径
     *
     * @param string $path Optionally, a path to append to the database path
     * @return string
     */
    public function databasePath($path = '')
    {
        return ($this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'db') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 获取资源目录的路径。
     *
     * @param string $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        // 无需实现
    }

    /**
     * 获取存储目录的路径。(对应tp框架的runtime)
     *
     * @return string
     */
    public function storagePath()
    {
        // 无需实现
    }


    /**
     * 获取或检查当前应用程序环境。
     *
     * @param string|array $environments
     * @return string|bool
     */
    public function environment(...$environments)
    {
        // 无需实现
    }

    /**
     * 确定应用程序是否正在控制台中运行。
     *
     * @return bool
     */
    public function runningInConsole()
    {
        // 无需实现

    }

    /**
     * 确定应用程序是否正在运行单元测试。
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        // 无需实现
    }

    /**
     * 确定应用程序当前是否已停机进行维护。
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        // 无需实现
    }

    /**
     * 注册所有配置的提供程序。
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        // 无需实现
    }

    /**
     * 向应用程序注册服务提供商。
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @param bool $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        // 无需实现
    }

    /**
     * 注册延迟提供程序和服务。
     *
     * @param string $provider
     * @param string|null $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        // 无需实现
    }

    /**
     * 从类名解析服务提供程序实例。
     *
     * @param string $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    public function resolveProvider($provider)
    {
        // 无需实现
    }

    /**
     * 启动应用程序的服务提供商。
     *
     * @return void
     */
    public function boot()
    {
        // 无需实现
    }

    /**
     * 注册一个新的启动侦听器。
     *
     * @param callable $callback
     * @return void
     */
    public function booting($callback)
    {
        // 无需实现
    }

    /**
     * 注册一个新的“已启动”侦听器。
     *
     * @param callable $callback
     * @return void
     */
    public function booted($callback)
    {
        // 无需实现
    }

    /**
     * 运行给定的引导程序类数组。
     *
     * @param array $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers)
    {
        // 无需实现
    }

    /**
     * 获取当前应用程序区域设置。
     *
     * @return string
     */
    public function getLocale()
    {
        // 无需实现
    }

    /**
     * 获取应用程序命名空间。
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (!is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);


        foreach ((array)data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array)$path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * 获取已注册的服务提供程序实例（如果存在）。
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @return array
     */
    public function getProviders($provider)
    {
        // 无需实现
    }

    /**
     * 确定应用程序以前是否已启动。
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        // 无需实现
    }

    /**
     * 加载并启动所有剩余的延迟提供程序。
     *
     * @return void
     */
    public function loadDeferredProviders()
    {
        // 无需实现
    }

    /**
     * 设置当前应用程序区域设置。
     *
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        // 无需实现
    }

    /**
     * 确定是否已为应用程序禁用中间件。
     *
     * @return bool
     */
    public function shouldSkipMiddleware()
    {
        // 无需实现
    }

    /**
     * 终止应用程序。
     *
     * @return void
     */
    public function terminate()
    {
        // 无需实现
    }



    // ==========非必须实现方法================

    /**
     * 获取应用程序“app”目录的路径。
     *
     * @param string $path
     * @return string
     */
    public function path($path = '')
    {
        $appPath = $this->appPath ?: $this->basePath . DIRECTORY_SEPARATOR . 'app';

        return $appPath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

}
