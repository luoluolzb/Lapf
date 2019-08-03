<?php

declare(strict_types=1);

namespace lqf\route;

/**
 * 路由trait
 * 继承 RouteInterface 的类可以使用此 trait
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
trait RouteTrait
{
    /**
     * @see RouteInterface::get
     */
    public function get(string $pattern, callable $handler): RouteInterface
    {
        return $this->map('GET', $pattern, $handler);
    }
    
    /**
     * @see RouteInterface::post
     */
    public function post(string $pattern, callable $handler): RouteInterface
    {
        return $this->map('POST', $pattern, $handler);
    }
    
    /**
     * @see RouteInterface::put
     */
    public function put(string $pattern, callable $handler): RouteInterface
    {
        return $this->map('PUT', $pattern, $handler);
    }
    
    /**
     * @see RouteInterface::delete
     */
    public function delete(string $pattern, callable $handler): RouteInterface
    {
        return $this->map('DELETE', $pattern, $handler);
    }
    
    /**
     * @see RouteInterface::patch
     */
    public function patch(string $pattern, callable $handler): RouteInterface
    {
        return $this->map('PATCH', $pattern, $handler);
    }

    /**
     * @see RouteInterface::any
     */
    public function any(string $pattern, callable $handler): RouteInterface
    {
        foreach (self::ALLOW_METHODS as $method => $value) {
            $this->mapOne($method, $pattern, $handler);
        }
        return $this;
    }
}
