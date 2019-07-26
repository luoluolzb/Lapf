<?php
namespace lqf\route;

use lqf\route\exception\RouteException;

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
     * @throws RouteException 路由异常
     * @return mixed          返回操作状态或者路由对象本身
     */
    public function add($method, string $pattern, callable $handler);

    /**
     * 开始路由调度
     *
     * @param  string $method   客户端请求方法
     * @param  string $pathInfo 客户端请求路径（不含查询串）
     *
     * @return DispatchResultInterface 路由调度结果
     */
    public function dispatch(string $method, string $pathInfo): DispatchResultInterface;
}
