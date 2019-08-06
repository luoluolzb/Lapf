<?php

declare(strict_types=1);

namespace Lqf\Route;

use \RuntimeException;
use \InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 路由器接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface RouterInterface extends CollectorInterface
{
    /**
     * 添加一个路由组
     *
     * @param  string   $prefix     路由组前缀
     * @param  callable $addHandler 路由添加器, 第一个参数为路由器实例本身
     *
     * @return RouterInterface
     */
    public function group(string $prefix, callable $addHandler): RouterInterface;

    /**
     * 开始路由调度
     *
     * @param  ServerRequestInterface  $request   客户端请求实例
     * @param  ResponseInterface       $response  客户端响应实例
     *
     * @return ResponseInterface 响应对象
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * 在上次添加的路由映射上添加一个中间件，如果指定第二个参数 $isGlobal 为 true
     * 会添加到全局映射上（每个请求都会调用全局中间件），默认为 false
     *
     * @param  mixed $middleware 中间件
     *
     * @return RouterInterface
     */
    public function middleware($middleware): RouterInterface;

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
