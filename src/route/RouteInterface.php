<?php

declare(strict_types=1);

namespace lqf\route;

use \RuntimeException;
use Psr\Http\Message\RequestInterface;

/**
 * 路由接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface RouteInterface
{
    /**
     * 添加一条路由规则
     *
     * @param string|array   $method  允许的一个或多个请求方法
     * @param string         $pattern 路由匹配规则
     * @param callable       $handler 路由处理器
     *
     * @throws RuntimeException 路由异常
     * @return mixed            返回操作状态或者路由对象本身
     */
    public function add($method, string $pattern, callable $handler);

    /**
     * 开始路由调度
     *
     * @param  RequestInterface $request   客户端请求
     *
     * @return DispatchResult 路由调度结果
     */
    public function dispatch(RequestInterface $request): DispatchResult;
}
