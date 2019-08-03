<?php

declare(strict_types=1);

namespace lqf\route;

use \UnexpectedValueException;
use \RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

/**
 * 路由类
 *
 * 此路由类基于 nikic/fast-route 实现
 * lqf 框架并不依赖此类，你可以实现 RouteInterface 接口编写自己的路由处理
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Route implements RouteInterface
{
    use RouteTrait;

    /**
     * 响应对象创建工厂
     *
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * 路由规则表
     *
     * 规则表结构：
     * [
     *     "{$method}{$pattern}" => [
     *         $method,
     *         $pattern,
     *         $handler
     *     ],
     * ]
     *
     * @var array
     */
    protected $rules;

    /**
     * 路由分组前缀
     * 用于添加路由分组
     *
     * @var string
     */
    private $groupPrefix;

    /**
     * 实例化路由类
     */
    public function __construct(ResponseFactoryInterface $factory)
    {
        $this->responseFactory = $factory;
        $this->rules = [];
        $this->groupPrefix = '';
    }

    /**
     * @see RouteInterface::map
     */
    public function map($method, string $pattern, callable $handler): RouteInterface
    {
        if (!\is_array($method)) {
            $this->mapOne($method, $pattern, $handler);
        } else {
            foreach ($method as &$value) {
                $this->mapOne($value, $pattern, $handler);
            }
        }
        return $this;
    }
    
    /**
     * @see RouteInterface::dispatch
     */
    public function dispatch(RequestInterface $request): ResponseInterface
    {
        $rules = &$this->rules;
        
        $routeAdd = function (RouteCollector $collector) use (&$rules) {
            foreach ($rules as &$rule) {
                $collector->addRoute($rule[0], $rule[1], $rule[2]);
            }
        };

        $dispatcher = simpleDispatcher($routeAdd);
        $info = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        
        $response = $this->responseFactory->createResponse();
        switch ($info[0]) {
            case Dispatcher::FOUND:
                $handler = $info[1];
                $params = $info[2];
                $response = $handler($request, $response, $params);
                if (!($response instanceof ResponseInterface)) {
                    throw new RuntimeException("The handler must be return instance of ResponseInterface");
                }
                break;
            
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowMethods = $info[1];
                $response = $response->withStatus(405);
                $response = $response->withHeader('Allow', $allowMethods);
                // $response = $response->withBody(
                //     $this->streamFactory->createStream("<h1>405 Method Not Allowed</h1>")
                // );
                break;

            case Dispatcher::NOT_FOUND:
                $response = $response->withStatus(404);
                // $response = $response->withBody(
                //     $this->streamFactory->createStream("<h1>404 Not Found</h1>")
                // );
                break;

            default:
                break;
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
     * 添加一个路由组
     *
     * @param  string   $prefix     路由组前缀
     * @param  callable $addRandler 路由添加器
     *
     * @return void
     */
    public function group(string $prefix, callable $addRandler): void
    {
        $this->groupPrefix = $prefix;
        $addRandler($this);
        $this->groupPrefix = '';
    }

    /**
     * @see RouteInterface::add
     */
    protected function mapOne(string $method, string $pattern, callable $handler): RouteInterface
    {
        $method = \strtoupper($method);
        if (!isset(self::ALLOW_METHODS[$method])) {
            throw new UnexpectedValueException("The request method {$method} is not allowed");
        }

        $pattern = $this->groupPrefix . $pattern;
        $key = $method . $pattern;
        
        if (isset($this->rules[$key])) {
            throw new RuntimeException("The route rule ({$method}, {$pattern}) already exists");
        }
        
        $this->rules[$key] = [$method, $pattern, $handler];
        return $this;
    }
}
