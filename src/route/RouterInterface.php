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
     * 开始路由调度
     *
     * @param ServerRequestInterface $request 客户端请求实例
     *
     * @return ResponseInterface 响应对象
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * 添加一个中间件
     *
     * @param mixed $middleware 中间件实例或类名
     *
     * @throws InvalidArgumentException 参数类型必须是中间件实例或类名
     * @return RouterInterface
     */
    public function middleware($middleware): RouterInterface;

    /**
     * 设置405错误请求处理
     *
     * @param callable|string $handler 处理器
     *
     * @throws InvalidArgumentException 处理器类型错误，必须是可调用的结构
     * @return void
     */
    public function setMethodNotAllowedHandler($handler): void;

    /**
     * 设置404错误请求处理
     *
     * @param callable|string $handler 处理器
     *
     * @throws InvalidArgumentException 处理器类型错误，必须是可调用的结构
     * @return void
     */
    public function setNotFoundHandler($handler): void;
}
