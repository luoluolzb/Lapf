<?php

declare(strict_types=1);

namespace lqf\route;

use \UnexpectedValueException;
use \RuntimeException;
use Psr\Http\Message\RequestInterface;
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
     * @see RouteInterface::dispatch
     */
    public function dispatch(RequestInterface $request): DispatchResult
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
        
        $res = new DispatchResult();
        switch ($info[0]) {
            case Dispatcher::NOT_FOUND:
                $res->setStatusCode(DispatchResult::NOT_FOUND);
                break;
            
            case Dispatcher::METHOD_NOT_ALLOWED:
                $res->setStatusCode(DispatchResult::METHOD_NOT_ALLOWED);
                $res->setAllowMethods($info[1]);
                break;
            
            case Dispatcher::FOUND:
                $res->setStatusCode(DispatchResult::FOUND);
                $res->setHandler($info[1]);
                $res->setParams($info[2]);
                break;
        }

        return $res;
    }

    /**
     * 魔术方法：添加请求方法为 $method 的路由规则
     * $method 必须要在 Route::ALLOW_METHODS 中
     *
     * @param  string   $method  请求方法
     * @param  array    $args    路由匹配模式和路由处理器
     *
     * @throws RuntimeException  路由异常
     * @return Route
     */
    public function __call(string $method, array $args): Route
    {
        return $this->addOne($method, $args[0], $args[1]);
    }

    /**
     * 添加全部请求方法的路由规则
     *
     * @param  string   $pattern 路由匹配规则
     * @param  callable $handler 路由处理器
     *
     * @throws RuntimeException  路由异常
     * @return Route
     */
    public function any(string $pattern, callable $handler): Route
    {
        foreach (self::ALLOW_METHODS as $method => $value) {
            $this->addOne($method, $pattern, $handler);
        }
        return $this;
    }

    /**
     * @see RouteInterface::add
     */
    protected function addOne(string $method, string $pattern, callable $handler): Route
    {
        $method = strtoupper($method);
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
