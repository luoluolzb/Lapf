<?php
declare(strict_types=1);

namespace Lqf\Route;

use Psr\Http\Message\RequestInterface;

/**
 * 路由调度器接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface DispatcherInterface
{
    /**
     * 路由调度
     *
     * @param RequestInterface $request 请求对象
     *
     * @return DispatchResult 路由调度结果
     */
    public function dispatch(RequestInterface $request): DispatchResult;
}
