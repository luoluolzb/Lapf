<?php
declare(strict_types=1);

namespace Lqf\Route;

use Psr\Http\Message\RequestInterface;
use FastRoute\RouteCollector as FastRouteCollector;
use FastRoute\Dispatcher as FastRouteDispatcher;
use function FastRoute\simpleDispatcher as fastSimpleDispatcher;

/**
 * 路由调度器
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * 快速路由的路由调度器
     *
     * @var FastRouteDispatcher
     */
    private $fastDispatcher;

    /**
     * 实例化一个路由调度器
     *
     * @param CollectorInterface $collector 路由收集器实例
     */
    public function __construct(CollectorInterface $collector)
    {
        $addHandler = function (FastRouteCollector $fastCollector) use ($collector) {
            foreach ($collector as $rule) {
                $fastCollector->addRoute($rule[0], $rule[1], $rule[2]);
            }
        };
        $this->fastDispatcher = fastSimpleDispatcher($addHandler);
    }

    /**
     * 路由调度
     *
     * @return DispatchResult 路由调度结果
     */
    public function dispatch(RequestInterface $request): DispatchResult
    {
        $info = $this->fastDispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        
        $result = new DispatchResult();
        switch ($info[0]) {
            case FastRouteDispatcher::FOUND:
                $result->setStatusCode(DispatchResult::FOUND);
                $result->setHandler($info[1]);
                $result->setParams($info[2]);
                break;
            
            case FastRouteDispatcher::METHOD_NOT_ALLOWED:
                $result->setStatusCode(DispatchResult::METHOD_NOT_ALLOWED);
                $result->setAllowMethods($info[1]);
                break;

            case FastRouteDispatcher::NOT_FOUND:
                $result->setStatusCode(DispatchResult::NOT_FOUND);
                break;

            default:
                break;
        }

        return $result;
    }
}
