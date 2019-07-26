<?php
namespace lqf\route;

use lqf\route\exception\StatusNotMatchException;

/**
 * 路由调度结果类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class DispatchResult implements DispatchResultInterface
{
    /**
     * 调度结果状态码
     *
     * 它的取值应该是下面几个值之一：
     * - NONE               初始值，表示未开始匹配
     * - NOT_FOUND          没找到当前请求的处理器
     * - METHOD_NOT_ALLOWED 找到当前请求的处理器，但是请求方法不允许
     * - FOUND              找到匹配的处理器
     *
     * @var int
     */
    private $statusCode;

    /**
     * 允许的请求方法列表
     *
     * 当结果状态码为 METHOD_NOT_ALLOWED 有效
     *
     * @var array
     */
    private $allowMethods;
    
    /**
     * 匹配的路由处理器
     *
     * 当结果状态码为 FOUND 有效
     *
     * @var callable
     */
    private $handler;

    /**
     * 路由中的参数
     *
     * 当结果状态码为 FOUND 有效
     *
     * @var array
     */
    private $params;

    /**
     * 实例化路由调度结果类
     */
    public function __construct()
    {
        $this->statusCode = self::NONE;
    }

    /**
     * @see DispatchResultInterface::getStatus
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @see DispatchResultInterface::getHandler
     */
    public function getHandler(): callable
    {
        if ($this->statusCode != self::FOUND) {
            throw new StatusNotMatchException('Status code must be FOUND for getHandler()');
        }
        return $this->handler;
    }

    /**
     * @see DispatchResultInterface::getAllowMethods
     */
    public function getAllowMethods(): array
    {
        if ($this->statusCode != self::METHOD_NOT_ALLOWED) {
            throw new StatusNotMatchException('Status code must be METHOD_NOT_ALLOWED for getAllowMethods()');
        }
        return $this->allowMethods ?? [];
    }

    /**
     * @see DispatchResultInterface::getParams
     */
    public function getParams(): array
    {
        if ($this->statusCode != self::FOUND) {
            throw new StatusNotMatchException('Status code must be FOUND for getParams()');
        }
        return $this->params ?? [];
    }

    /**
     * 设置状态码
     *
     * @param int $statusCode 状态码
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * 设置允许的请求方法
     *
     * @param array $allowMethods 不允许的方法列表
     */
    public function setAllowMethods(array $allowMethods): void
    {
        $this->allowMethods = $allowMethods;
    }

    /**
     * 设置路由处理器
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
