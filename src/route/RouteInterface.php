<?php

declare(strict_types=1);

namespace Lqf\Route;

use \RuntimeException;
use \InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * 路由接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface RouteInterface
{
    /**
     * 添加一条路由映射
     *
     * @param string|array   $method  允许的一个或多个请求方法
     * @param string         $pattern 路由匹配规则
     * @param callable       $handler 路由处理器
     *
     * @throws RuntimeException         路由异常
     * @throws InvalidArgumentException 无效的method参数类型
     * @return RouteInterface           路由对象本身
     */
    public function map($method, string $pattern, callable $handler): RouteInterface;

    /**
     * 添加一条请求方法为GET的路由映射
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function get(string $pattern, callable $handler): RouteInterface;
    
    /**
     * 添加一条请求方法为 GET 的路由映射
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function post(string $pattern, callable $handler): RouteInterface;
    
    /**
     * 添加一条请求方法为 PUT 的路由映射
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function put(string $pattern, callable $handler): RouteInterface;
    
    /**
     * 添加一条请求方法为 DELETE 的路由
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function delete(string $pattern, callable $handler): RouteInterface;
    
    /**
     * 添加一条请求方法为 PATCH 的路由映射
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function patch(string $pattern, callable $handler): RouteInterface;

    /**
     * 添加一条请求方法任意的路由映射
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return RouteInterface   路由对象本身
     */
    public function any(string $pattern, callable $handler): RouteInterface;

    /**
     * 添加一个路由组
     *
     * @param  string   $prefix     路由组前缀
     * @param  callable $addRandler 路由添加器
     *
     * @return void
     */
    public function group(string $prefix, callable $addRandler): void;

    /**
     * 开始路由调度
     *
     * @param  RequestInterface  $request   客户端请求实例
     * @param  ResponseInterface $response  客户端响应实例
     *
     * @return ResponseInterface 响应对象
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * 在上次添加的路由映射上添加一个中间件，如果指定第二个参数 $isGlobal 为 true
     * 会添加到全局映射上（每个请求都会调用全局中间件），默认为 false
     *
     * @param  MiddlewareInterface $middleware 中间件
     * @param  bool                $isGlobal   是否添加到全局(默认为 false)
     *
     * @return void
     */
    public function middleware(MiddlewareInterface $middleware, bool $isGlobal = false): void;

    /**
     * 设置405错误请求处理
     *
     * @param callable $handler 处理器
     */
    public function setMethodNotAllowedHandler(callable $handler): void;

    /**
     * 设置404错误请求处理
     *
     * @param callable $handler 处理器
     */
    public function setNotFoundHandler(callable $handler): void;
}
