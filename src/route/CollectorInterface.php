<?php

declare(strict_types=1);

namespace Lqf\Route;

use \Iterator;
use \RuntimeException;
use \InvalidArgumentException;

/**
 * 路由规则收集器接口
 * 此接口继承了迭代器接口，所以可以使用 foreach 遍历收集器实例
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface CollectorInterface extends Iterator
{
    /**
     * 允许注册路由规则的请求方法
     */
    public const ALLOW_METHODS = [
        'GET'     => true,
        'POST'    => true,
        'PUT'     => true,
        'DELETE'  => true,
        'PATCH'   => true,
        'HEAD'    => true,
        'OPTIONS' => true,
    ];

    /**
     * 添加一条路由映射
     *
     * @param string|array    $method  允许的一个或多个请求方法
     * @param string          $pattern 路由匹配规则
     * @param callable|string $handler 路由处理器
     *
     * @throws RuntimeException         路由异常
     * @throws InvalidArgumentException 无效的method参数类型
     * @return CollectorInterface
     */
    public function map($method, string $pattern, $handler): CollectorInterface;

    /**
     * 添加一条请求方法为 GET 的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function get(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 POST 的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function post(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 PUT 的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function put(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 DELETE 的路由
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function delete(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 PATCH 的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function patch(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 HEAD 的路由
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function head(string $pattern, $handler): CollectorInterface;
    
    /**
     * 添加一条请求方法为 OPTIONS 的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function options(string $pattern, $handler): CollectorInterface;

    /**
     * 添加一条请求方法为任意的路由映射
     *
     * @param  string          $pattern 路由匹配规则
     * @param  callable|string $handler 路由处理器
     *
     * @throws RuntimeException    路由异常
     * @return CollectorInterface  对象本身
     */
    public function any(string $pattern, $handler): CollectorInterface;

    /**
     * 添加一个路由组
     *
     * @param  string   $prefix     路由组前缀
     * @param  callable $addHandler 路由添加器, 参数为收集器实例
     *
     * @return CollectorInterface
     */
    public function group(string $prefix, callable $addHandler): CollectorInterface;
}
