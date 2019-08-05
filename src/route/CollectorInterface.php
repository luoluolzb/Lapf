<?php
declare(strict_types=1);

namespace lqf\route;

use \Iterator;
use \RuntimeException;
use \InvalidArgumentException;

/**
 * 路由规则收集器接口
 * 此接口集成了迭代器接口，由此可以使用 foreach 遍历实现收集器接口的类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface CollectorInterface extends Iterator
{
    /**
     * 允许注册路由规则的请求方法
     *
     * 不要将 true 修改为 false，如果要增加减少
     * 允许的请求方法，应该删除或者增加键值对
     */
    public const ALLOW_METHODS = [
        'GET'    => true,
        'POST'   => true,
        'PUT'    => true,
        'DELETE' => true,
        'PATCH'  => true,
    ];

    /**
     * 添加一条路由映射
     *
     * @param string|array   $method  允许的一个或多个请求方法
     * @param string         $pattern 路由匹配规则
     * @param callable       $handler 路由处理器
     *
     * @throws RuntimeException         路由异常
     * @throws InvalidArgumentException 无效的method参数类型
     * @return void
     */
    public function map($method, string $pattern, callable $handler): void;
}
