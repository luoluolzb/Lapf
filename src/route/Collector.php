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
    private $rules;

    /**
     * 实例化一个路由收集器
     */
    public function __construct()
    {
        $this->rules = [];
    }

    /**
     * @see CollectorInterface::map
     */
    public function map($method, string $pattern, callable $handler): void
    {
        if (\is_string($method)) {
            $method = \strtoupper($method);
            if (!isset(self::ALLOW_METHODS[$method])) {
                throw new UnexpectedValueException("The request method {$method} is not allowed");
            }
            
            $key = "{$method} {$pattern}";
            if (isset($this->rules[$key])) {
                throw new RuntimeException("The route rule ({$method}, {$pattern}) already exists");
            }
            
            $this->rules[$key] = [$method, $pattern, $handler];
        } elseif (\is_array($method)) {
            foreach ($method as &$value) {
                $this->map($value, $pattern, $handler);
            }
        } else {
            throw new InvalidArgumentException("The 'method' argument must be string or string[]");
        }
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
}
