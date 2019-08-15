<?php

declare(strict_types=1);

namespace Lqf\Route;

use \RuntimeException;
use \InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Relay\Relay;

/**
 * 路由器
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Router extends Collector implements RouterInterface
{
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
        $this->middlewareQueue = [];
    }
    
    /**
     * @see RouteInterface::dispatch
     * @throws RuntimeException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $result   = (new Dispatcher($this))->dispatch($request);
        $handler  = null;
        $params   = [];
        
        switch ($result->getStatusCode()) {
            case DispatchResult::FOUND:
                $handler  = $result->getHandler();
                $params   = $result->getParams();
                break;
            
            case DispatchResult::METHOD_NOT_ALLOWED:
                $allowMethods = $result->getAllowMethods();
                $handler  = $this->methodNotAllowedHandler;
                $params   = $allowMethods;
                break;

            case DispatchResult::NOT_FOUND:
                $handler  = $this->notFoundHandler;
                break;
        }

        // 将路由处理器放到中间件队列末尾
        $this->middlewareQueue[] = function (
            ServerRequestInterface $request,
            $next
        ) use (
            $handler,
            $params
        ) {
            if (\is_string($handler)) {
                list($controllerName, $actionName) = \explode("::", $handler);
                $controller = new $controllerName();
                return $controller->$actionName($request, $params);
            } elseif (\is_callable($handler)) {
                return $handler($request, $params);
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
        if (!\is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new InvalidArgumentException("The middleware must be subclass of MiddlewareInterface");
        }
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
