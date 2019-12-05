<?php
declare(strict_types=1);

namespace Lqf;

/**
 * 环境类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Envir
{
    /**
     * 服务器信息，通常是 $_SERVER
     *
     * @var array
     */
    private $server = [];

    /**
     * 环境参数，通常是 $_ENV
     *
     * @var array
     */
    private $env = [];

    /**
     * GET 参数，通常是 $_GET
     *
     * @var array
     */
    private $get = [];

    /**
     * POST 参数，通常是 $_POST
     *
     * @var array
     */
    private $post = [];

    /**
     * cookie 参数，通常是 $_COOKIE
     *
     * @var array
     */
    private $cookie = [];

    /**
     * 上传文件信息，通常是 $_FILES
     *
     * @var array
     */
    private $files = [];

    /**
     * 实例化环境参数
     * 
     * @param array $server 服务器参数
     * @param array $env    环境参数
     * @param array $get    get参数
     * @param array $post   post参数
     * @param array $cookie cookie参数
     * @param array $files  files参数
     */
    public function __construct(
        array $server = null,
        array $env = null,
        array $get = null,
        array $post = null,
        array $cookie = null,
        array $files = null
    ) {
        $this->setServer($server ?? $_SERVER);
        $this->setEnv($env ?? $_ENV);
        $this->setGet($get ?? $_GET);
        $this->setPost($post ?? $_POST);
        $this->setCookie($cookie ?? $_COOKIE);
        $this->setFiles($files ?? $_FILES);
    }

    /**
     * 设置服务器参数
     *
     * @param array $server 服务器参数
     *
     * @return void
     */
    public function setServer(array $server): void
    {
        $this->server = $server;
    }

    /**
     * 设置环境参数
     *
     * @param array $server 环境参数
     *
     * @return void
     */
    public function setEnv(array $env): void
    {
        $this->env = $env;
    }

    /**
     * 设置GET参数
     *
     * @param array $get GET参数
     *
     * @return void
     */
    public function setGet(array $get): void
    {
        $this->get = $get;
    }

    /**
     * 设置POST参数
     *
     * @param array $post POST参数
     *
     * @return void
     */
    public function setPost(array $post): void
    {
        $this->post = $post;
    }

    /**
     * 设置cookie参数
     *
     * @param array $cookie cookie参数
     *
     * @return void
     */
    public function setCookie(array $cookie): void
    {
        $this->cookie = $cookie;
    }

    /**
     * 设置上传文件参数
     *
     * @param array $files 上传文件参数
     *
     * @return void
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * 获取一个或全部服务器参数
     *
     * @param  string|null $name 参数名，默认获取全部
     *
     * @return mixed
     */
    public function server(string $name = null)
    {
        if (isset($this->server)) {
            return isset($name) ? ($this->server[$name] ?? null) : $this->server;
        } else {
            return null;
        }
    }

    /**
     * 获取一个或全部环境参数
     *
     * @param  string|null $name 参数名，默认获取全部
     *
     * @return mixed
     */
    public function env(string $name = null)
    {
        if (isset($this->env)) {
            return isset($name) ? ($this->env[$name] ?? null) : $this->env;
        } else {
            return null;
        }
    }

    /**
     * 获取一个或全部GET参数
     *
     * @param  string|null $name 参数名，默认获取全部
     *
     * @return mixed
     */
    public function get(string $name = null)
    {
        if (isset($this->get)) {
            return isset($name) ? ($this->get[$name] ?? null) : $this->get;
        } else {
            return null;
        }
    }

    /**
     * 获取一个或全部POST参数
     *
     * @param  string|null $name 参数名，默认获取全部
     *
     * @return mixed
     */
    public function post(string $name = null)
    {
        if (isset($this->post)) {
            return isset($name) ? ($this->post[$name] ?? null) : $this->post;
        } else {
            return null;
        }
    }

    /**
     * 获取一个或全部cookie参数
     *
     * @param  string|null $name 参数名，默认获取全部
     *
     * @return mixed
     */
    public function cookie(string $name = null)
    {
        if (isset($this->cookie)) {
            return isset($name) ? ($this->cookie[$name] ?? null) : $this->cookie;
        } else {
            return null;
        }
    }

    /**
     * 获取一个或全部上传文件信息
     *
     * @param  string|null $name input表单的name，默认获取全部
     *
     * @return mixed
     */
    public function files(string $name = null)
    {
        if (isset($this->files)) {
            return isset($name) ? ($this->files[$name] ?? null) : $this->files;
        } else {
            return null;
        }
    }
}
