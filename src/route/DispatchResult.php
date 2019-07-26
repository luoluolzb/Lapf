<?php
namespace lqf\route;

use lqf\route\DispatchResultInterface;
use lqf\route\exception\StatusNotMatchException;

/**
 * 路由调度结果类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class DispatchResult implements DispatchResultInterface
{
    private $status;
    private $allowMethods;
    private $handler;
    private $params;

    public function __construct()
    {
        $this->status = self::NONE;
    }

    /**
     * @see DispatchResultInterface::getStatus
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @see DispatchResultInterface::getHandler
     */
    public function getHandler(): callable
    {
        if ($this->status != self::FOUND) {
            throw new StatusNotMatchException("Status must be FOUND");
        }
        return $this->handler;
    }

    /**
     * @see DispatchResultInterface::getAllowMethods
     */
    public function getAllowMethods(): array
    {
        if ($this->status != self::METHOD_NOT_ALLOWED) {
            throw new StatusNotMatchException("Status must be METHOD_NOT_ALLOWED");
        }
        return $this->allowMethods ?? [];
    }

    /**
     * @see DispatchResultInterface::getParams
     */
    public function getParams(): array
    {
        if ($this->status != self::FOUND) {
            throw new StatusNotMatchException("Status must be FOUND");
        }
        return $this->params ?? [];
    }

    /**
     * 设置状态码
     * 状态码值应该是下面几个值之一：
     * - NOT_FOUND          没找到当前请求的处理器
     * - METHOD_NOT_ALLOWED 找到当前请求的处理器，但是请求方法不允许
     * - FOUND              找到匹配的处理器
     *
     * @param int $status 状态码
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * 设置不允许的方法
     *
     * @param array $allowMethods 不允许的方法列表
     */
    public function setAllowMethods(array $allowMethods): void
    {
        $this->allowMethods = $allowMethods;
    }

    /**
     * 设置处理器
     *
     * @param callable $handler 处理器
     */
    public function setHandler(callable $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * 设置路由参数
     *
     * @param array $params 路由参数
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}
