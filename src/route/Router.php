<?php

declare(strict_types=1);

namespace Lqf\Route;

use \UnexpectedValueException;
use \RuntimeException;
use \InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Relay\Relay;

/**
 * 路由器
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Router extends Collector implements RouterInterface
{
    /**
     * 路由分组前缀
     * 用于添加路由分组
     *
     * @var string
     */
    private $groupPrefix;

    /**
     * 路由中间队列
     *
     * @var array
     */
    private $middlewareQueue;

    /**
     * 405错误处理器
     *
     * @var callable|string
     */
    private $methodNotAllowedHandler;

    /**
     * 404错误处理器
     *
     * @var callable|string
     */
    private $notFoundHandler;

    /**
     * 实例化路由类
     */
    public function __construct()
    {
        parent::__construct();
        $this->groupPrefix = '';
        $this->middlewareQueue = [];
    }

    /**
     * @see RouteInterface::map
     */
    public function map($method, string $pattern, $handler): CollectorInterface
    {
        return parent::map($method, $this->groupPrefix . $pattern, $handler);
    }

    /**
     * @see RouteInterface::group
     */
    public function group(string $prefix, callable $addHandler): RouterInterface
    {
        $originPrefix = $this->groupPrefix;
        $this->groupPrefix = $originPrefix . $prefix;
        $addHandler($this);
        $this->groupPrefix = $originPrefix;
        return $this;
    }
    
    /**
     * @see RouteInterface::dispatch
     * @throws RuntimeException
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $dispatcher = new Dispatcher($this);

        $result = $dispatcher->dispatch($request);
        $handler = null;
        $params = [];
        
        switch ($result->getStatusCode()) {
            case DispatchResult::FOUND:
                $handler = $result->getHandler();
                $params = $result->getParams();
                break;
            
            case DispatchResult::METHOD_NOT_ALLOWED:
                $allowMethods = $result->getAllowMethods();
                $response = $response->withStatus(405);
                $response = $response->withHeader('Allow', implode(', ', $allowMethods));
                $handler = $this->methodNotAllowedHandler;
                $params = $allowMethods;
                break;

            case DispatchResult::NOT_FOUND:
                $response = $response->withStatus(404);
                $handler = $this->notFoundHandler;
                break;

            default:
                break;
        }

        // 将路由处理器放到中间件队列末尾
        $this->middlewareQueue[] = function (
            ServerRequestInterface $request,
            $next
        ) use (
            $handler,
            $response,
            $params
        ) {
            if (\is_string($handler)) {
                list($controllerName, $actionName) = \explode("::", $handler);
                $controller = new $controllerName();
                return $controller->$actionName($request, $response, $params);
            } elseif (\is_callable($handler)) {
                return $handler($request, $response, $params);
            } else {
                throw new RuntimeException("The route handler must be callable");
            }
        };

        // 调度中间件队列
        $relay = new Relay($this->middlewareQueue, new MiddlewareResolver);
        $response = $relay->handle($request);

        return $response;
    }

    /**
     * @see RouteInterface::middleware
     */
    public function middleware($middleware): RouterInterface
    {
        $this->middlewareQueue[] = $middleware;
        return $this;
    }

    /**
     * @see RouteInterface::setMethodNotAllowedHandler
     */
    public function setMethodNotAllowedHandler($handler): void
    {
        if (!\is_callable($handler) && !\is_string($handler)) {
            throw new InvalidArgumentException("The handler must be callable");
        }
        $this->methodNotAllowedHandler = $handler;
    }

    /**
     * @see RouteInterface::setNotFoundHandler
     */
    public function setNotFoundHandler($handler): void
    {
        if (!\is_callable($handler) && !\is_string($handler)) {
            throw new InvalidArgumentException("The handler must be callable");
        }
        $this->notFoundHandler = $handler;
    }
}
