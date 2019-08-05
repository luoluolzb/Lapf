<?php
declare(strict_types=1);

namespace Lqf\Route;

use \BadMethodCallException;

/**
 * 路由调度结果
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class DispatchResult
{
    /**
     * 路由调度结果状态码：无（未开始匹配）
     *
     * 应该用此作为状态码初始值
     */
    public const NONE = 0;

    /**
     * 路由调度结果状态码：没找到当前请求的处理器
     */
    public const NOT_FOUND = 1;
    
    /**
     * 路由调度结果状态码：找到当前请求的处理器，但是请求方法不允许
     */
    public const METHOD_NOT_ALLOWED = 2;
    
    /**
     * 路由调度结果状态码：找到匹配的处理器
     */
    public const FOUND = 3;

    /**
     * 调度结果状态码
     *
     * 它的取值应该是下面几个值之一：
     * - NONE
     * - NOT_FOUND
     * - METHOD_NOT_ALLOWED
     * - FOUND
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
     * 获取路由调度结果状态码
     *
     * @return int 状态码
     * @return int 路由调度状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 如果状态码为 FOUND，返回相应的处理器
     * 状态码为 FOUND 时，返回相应的处理器
     *
     * @throws BadMethodCallException 状态码不匹配
     * @return callable
     * @return callable 路由处理器
     */
    public function getHandler(): callable
    {
        if ($this->statusCode !== self::FOUND) {
            throw new BadMethodCallException('The status code must be FOUND for getHandler()');
        }
        return $this->handler;
    }
    
    /**
     * 如果状态码为 METHOD_NOT_ALLOWED，返回允许的请求方法列表
     * 如果状态码为 METHOD_NOT_ALLOWED 时，返回允许的请求方法列表
     *
     * @throws BadMethodCallException 状态码不匹配
     * @return array 允许的请求方法列表
     */
    public function getAllowMethods(): array
    {
        if ($this->statusCode !== self::METHOD_NOT_ALLOWED) {
            throw new BadMethodCallException('The status must be METHOD_NOT_ALLOWED for call getAllowMethods()');
        }
        return $this->allowMethods ?? [];
    }

    /**
     * 如果状态码为 FOUND ，返回路由中的参数
     * 如果状态码为 FOUND 时，返回路由中的参数
     *
     * @throws BadMethodCallException 状态码不匹配
     * @return array 路由参数
     */
    public function getParams(): array
    {
        if ($this->statusCode !== self::FOUND) {
            throw new BadMethodCallException('The status must be FOUND for call getParams()');
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
