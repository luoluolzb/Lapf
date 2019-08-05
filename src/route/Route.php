<?php

declare(strict_types=1);

namespace Lqf\Route;

use \UnexpectedValueException;
use \RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * 路由类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Route implements RouteInterface
{
    use RouteTrait;

    /**
     * 路由分组前缀
     * 用于添加路由分组
     *
     * @var string
     */
    private $groupPrefix;

    /**
     * 路由收集器对象
     *
     * @var Collector
     */
    private $collector;

    /**
     * 405错误处理器
     *
     * @var null|callable
     */
    private $methodNotAllowedHandler;

    /**
     * 404错误处理器
     *
     * @var null|callable
     */
    private $notFoundHandler;

    /**
     * 实例化路由类
     */
    public function __construct()
    {
        $this->groupPrefix = '';
        $this->collector = new Collector();
    }

    /**
     * @see RouteInterface::map
     */
    public function map($method, string $pattern, callable $handler): RouteInterface
    {
        $this->collector->map($method, $this->groupPrefix . $pattern, $handler);
        return $this;
    }

    /**
     * @see RouteInterface::group
     */
    public function group(string $prefix, callable $addRandler): void
    {
        $originPrefix = $this->groupPrefix;
        $this->groupPrefix = $originPrefix . $prefix;
        $addRandler($this);
        $this->groupPrefix = $originPrefix;
    }
    
    /**
     * @see RouteInterface::dispatch
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $dispatcher = new Dispatcher($this->collector);
        $result = $dispatcher->dispatch($request);
        
        switch ($result->getStatusCode()) {
            case DispatchResult::FOUND:
                $handler = $result->getHandler();
                $params = $result->getParams();
                $response = $handler($request, $response, $params);
                break;
            
            case DispatchResult::METHOD_NOT_ALLOWED:
                $allowMethods = $result->getAllowMethods();
                $response = $response->withStatus(405);
                $response = $response->withHeader('Allow', implode(', ', $allowMethods));
                $handler = $this->methodNotAllowedHandler;
                if ($handler) {
                    $response = $handler($request, $response, $allowMethods);
                }
                break;

            case DispatchResult::NOT_FOUND:
                $response = $response->withStatus(404);
                $handler = $this->notFoundHandler;
                if ($handler) {
                    $response = $handler($request, $response);
                }
                break;

            default:
                break;
        }

        if (!($response instanceof ResponseInterface)) {
            throw new RuntimeException("The handler must be return instance of ResponseInterface");
        }
        return $response;
    }

    /**
     * @see RouteInterface::middleware
     */
    public function middleware(MiddlewareInterface $middleware, bool $isGlobal = false): void
    {
    }

    /**
     * @see RouteInterface::setMethodNotAllowedHandler
     */
    public function setMethodNotAllowedHandler(callable $handler): void
    {
        $this->methodNotAllowedHandler = $handler;
    }

    /**
     * @see RouteInterface::setNotFoundHandler
     */
    public function setNotFoundHandler(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }
}
