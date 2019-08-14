<?php

declare(strict_types=1);

namespace Lqf;

use \RuntimeException;

/**
 * 环境类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Env
{
    /**
     * 服务器和执行环境信息，一般从 $_SERVER 获取
     *
     * @var array
     */
    private $server;

    /**
     * 环境参数，一般从 $_ENV 获取
     *
     * @var array
     */
    private $env;

    /**
     * cookie 参数，一般从 $_COOKIE 获取
     *
     * @var array
     */
    private $cookie;

    /**
     * HTTP 上传文件信息，一般从 $_FILES 获取
     *
     * @var array
     */
    private $files;

    /**
     * 实例化环境类
     */
    public function __construct(
        array $server,
        array $env,
        array $cookie,
        array $files
    ) {
        $this->server = $server;
        $this->env    = $env;
        $this->cookie = $cookie;
        $this->files  = $files;
    }

    /**
     * 获取一个或全部服务器参数
     *
     * @param  string|null $name 参数名，默认null获取全部
     *
     * @return mixed
     */
    public function server(string $name = null)
    {
        return isset($name) ? ($this->server[$name] ?? null) : $this->server;
    }

    /**
     * 获取一个或全部环境参数
     *
     * @param  string|null $name 参数名，默认null获取全部
     *
     * @return mixed
     */
    public function env(string $name = null)
    {
        return isset($name) ? ($this->env[$name] ?? null) : $this->env;
    }

    /**
     * 获取一个或全部cookie参数
     *
     * @param  string|null $name 参数名，默认null获取全部
     *
     * @return mixed
     */
    public function cookie(string $name = null)
    {
        return isset($name) ? ($this->cookie[$name] ?? null) : $this->cookie;
    }

    /**
     * 获取一个或全部上传文件信息
     *
     * @param  string|null $name input表单的name，默认null获取全部
     *
     * @return mixed
     */
    public function files(string $name = null)
    {
        return isset($name) ? ($this->files[$name] ?? null) : $this->files;
    }
}
