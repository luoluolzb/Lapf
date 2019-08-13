<?php

declare(strict_types=1);

namespace Lqf\Route;

use \UnexpectedValueException;
use \RuntimeException;
use \InvalidArgumentException;

/**
 * 路由规则收集器
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Collector implements CollectorInterface
{
    use CollectorTrait;

    /**
     * 路由规则表
     *
     * 规则表结构：
     * [
     *     "{$method}{$pattern}" => [
     *         $method,
     *         $pattern,
     *         $handler,
     *     ],
     * ]
     *
     * @var array
     */
    private $rules;

    /**
     * 路由分组前缀
     * 用于添加路由分组
     *
     * @var string
     */
    private $groupPrefix;

    /**
     * 实例化一个路由收集器
     */
    public function __construct()
    {
        $this->rules = [];
        $this->groupPrefix = '';
    }

    /**
     * @see CollectorInterface::map
     */
    public function map($method, string $pattern, $handler): CollectorInterface
    {
        if (\is_string($method)) {  // 只注册一个请求方法
            $this->mapOne($method, $this->groupPrefix . $pattern, $handler);
        } elseif (\is_array($method)) {  // 注册多个请求方法
            $pattern = $this->groupPrefix . $pattern;
            foreach ($method as &$value) {
                $this->mapOne($value, $pattern, $handler);
            }
        } else {
            throw new InvalidArgumentException("The 'method' argument must be string or string[]");
        }
        return $this;
    }

    /**
     * @see CollectorInterface::group
     */
    public function group(string $prefix, callable $addHandler): CollectorInterface
    {
        $originPrefix = $this->groupPrefix;
        $this->groupPrefix = $originPrefix . $prefix;
        $addHandler($this);
        $this->groupPrefix = $originPrefix;
        return $this;
    }

    /**
     * @see CollectorInterface::rewind
     */
    public function rewind()
    {
        \reset($this->rules);
    }

    /**
     * @see CollectorInterface::current
     */
    public function current()
    {
        return \current($this->rules);
    }

    /**
     * @see CollectorInterface::key
     */
    public function key()
    {
        return \key($this->rules);
    }

    /**
     * @see CollectorInterface::next
     */
    public function next()
    {
        \next($this->rules);
    }

    /**
     * @see CollectorInterface::valid
     */
    public function valid()
    {
        return false !== \current($this->rules);
    }

    /**
     * 添加一条请求方法的路由映射
     *
     * @param string          $method  允许的一个或多个请求方法
     * @param string          $pattern 路由匹配规则
     * @param callable|string $handler 路由处理器
     *
     * @throws RuntimeException         路由异常
     */
    public function mapOne(string $method, string $pattern, $handler): void
    {
        $method = \strtoupper($method);
        if (!isset(self::ALLOW_METHODS[$method])) {
            throw new UnexpectedValueException("The request method {$method} is not allowed");
        }

        $key = "{$method} {$pattern}";
        if (isset($this->rules[$key])) {
            throw new RuntimeException("The route rule ({$method}, {$pattern}) already exists");
        }

        $this->rules[$key] = [$method, $pattern, $handler];
    }
}
