<?php
declare(strict_types=1);

namespace Lqf\Route;

/**
 * 路由规则收集器 trait
 * 继承 CollectorInterface 的类可以使用此 trait
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
trait CollectorTrait
{
    /**
     * @see CollectorInterface::get
     */
    public function get(string $pattern, $handler): CollectorInterface
    {
        return $this->map('GET', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::post
     */
    public function post(string $pattern, $handler): CollectorInterface
    {
        return $this->map('POST', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::put
     */
    public function put(string $pattern, $handler): CollectorInterface
    {
        return $this->map('PUT', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::delete
     */
    public function delete(string $pattern, $handler): CollectorInterface
    {
        return $this->map('DELETE', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::patch
     */
    public function patch(string $pattern, $handler): CollectorInterface
    {
        return $this->map('PATCH', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::head
     */
    public function head(string $pattern, $handler): CollectorInterface
    {
        return $this->map('HEAD', $pattern, $handler);
    }
    
    /**
     * @see CollectorInterface::options
     */
    public function options(string $pattern, $handler): CollectorInterface
    {
        return $this->map('OPTIONS', $pattern, $handler);
    }

    /**
     * @see CollectorInterface::any
     */
    public function any(string $pattern, $handler): CollectorInterface
    {
        foreach (self::ALLOW_METHODS as $method => $value) {
            $this->map($method, $pattern, $handler);
        }
        return $this;
    }
}
