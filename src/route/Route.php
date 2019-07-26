<?php
namespace lqf\route;

use lqf\route\RouteInterface;
use lqf\route\DispatchResult;
use lqf\route\exception\HttpMethodNotAllowedException;
use lqf\route\exception\HandlerExistsException;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

/**
 * 路由类
 *
 * 此路由类基于 nikic/fast-route 实现
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Route implements RouteInterface
{
    /**
     * 允许注册路由规则的请求方法
     */
    public const ALLOW_METHODS = [
        'GET'    => true,
        'POST'   => true,
        'PUT'    => true,
        'DELETE' => true,
        'PATCH'  => true,
    ];

    /**
     * 路由规则列表
     * (method, pattern) => handler
     *
     * 规则结构
     * [
     *     '(method, pattern)' => [
     *         method,
     *         pattern,
     *         handler
     *     ],
     * ]
     *
     * @var array
     */
    protected $rules;

    /**
     * 路由分组前缀
     *
     * @var string
     */
    private $groupPrefix;

    /**
     * 实例化路由类
     */
    public function __construct()
    {
        $this->rules = [];
        $this->groupPrefix = '';
    }

    /**
     * @see RouteInterface::add
     */
    public function add($method, string $pattern, callable $handler): Route
    {
        if (!is_array($method)) {
            $this->addOne($method, $pattern, $handler);
        } else {
            foreach ($method as &$value) {
                $this->addOne($value, $pattern, $handler);
            }
        }
        return $this;
    }

    /**
     * 添加一个路由组
     *
     * @param  string   $prefix     组前缀
     * @param  callable $addRandler 添加器
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
     * @see RouteInterface::dispatch
     */
    public function dispatch(string $method, string $pathinfo): DispatchResultInterface
    {
        $rules = &$this->rules;
        
        $routeAdd = function (RouteCollector $collector) use (&$rules) {
            foreach ($rules as &$rule) {
                $collector->addRoute($rule[0], $rule[1], $rule[2]);
            }
        };

        $dispatcher = simpleDispatcher($routeAdd);
        $routeInfo = $dispatcher->dispatch($method, $pathinfo);
        $dispatchResult = new DispatchResult();

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $dispatchResult->setStatus(DispatchResult::NOT_FOUND);
                break;
            
            case Dispatcher::METHOD_NOT_ALLOWED:
                $dispatchResult->setStatus(DispatchResult::METHOD_NOT_ALLOWED);
                $dispatchResult->setAllowMethods($routeInfo[1]);
                break;
            
            case Dispatcher::FOUND:
                $dispatchResult->setStatus(DispatchResult::FOUND);
                $dispatchResult->setHandler($routeInfo[1]);
                $dispatchResult->setParams($routeInfo[2]);
                break;
        }

        return $dispatchResult;
    }

    /**
     * 添加请求方法为 GET 的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function get(string $pattern, callable $handler): Route
    {
        return $this->addOne('GET', $pattern, $handler);
    }

    /**
     * 添加请求方法为 POST 的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function post(string $pattern, callable $handler): Route
    {
        return $this->addOne('POST', $pattern, $handler);
    }

    /**
     * 添加请求方法为 PUT 的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function put(string $pattern, callable $handler): Route
    {
        return $this->addOne('PUT', $pattern, $handler);
    }

    /**
     * 添加请求方法为 PATCH 的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function patch(string $pattern, callable $handler): Route
    {
        return $this->addOne('PATCH', $pattern, $handler);
    }

    /**
     * 添加请求方法为 DELETE 的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function delete(string $pattern, callable $handler): Route
    {
        return $this->addOne('DELETE', $pattern, $handler);
    }

    /**
     * 添加全部请求方法的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @return Route
     */
    public function any(string $pattern, callable $handler): Route
    {
        $this->addOne('GET', $pattern, $handler);
        $this->addOne('POST', $pattern, $handler);
        $this->addOne('PUT', $pattern, $handler);
        $this->addOne('PATCH', $pattern, $handler);
        $this->addOne('DELETE', $pattern, $handler);
        return $this;
    }

    /**
     * @see RouteInterface::add
     */
    protected function addOne(string $method, string $pattern, callable $handler): Route
    {
        $method = strtoupper($method);
        if (!isset(self::ALLOW_METHODS[$method])) {
            throw new HttpMethodNotAllowedException("The handler for ({$method}, {$pattern}) already exists");
        }

        $pattern = $this->groupPrefix . $pattern;
        $key = $method . $pattern;
        
        if (isset($this->rules[$key])) {
            throw new HandlerExistsException("The handler for ({$method}, {pattern}) already exists");
        }
        
        $this->rules[$key] = [$method, $pattern, $handler];
        return $this;
    }
}
