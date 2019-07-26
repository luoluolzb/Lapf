<?php
namespace lqf\route;

use lqf\route\exception\StatusNotMatchException;

/**
 * 路由调度结果接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface DispatchResultInterface
{
    /**
     * 路由结果状态码：无，未开始匹配
     */
    public const NONE = 0;

    /**
     * 路由结果状态码：没找到当前请求的处理器
     */
    public const NOT_FOUND = 1;
    
    /**
     * 路由结果状态码：找到当前请求的处理器，但是请求方法不允许
     */
    public const METHOD_NOT_ALLOWED = 2;
    
    /**
     * 路由结果状态码：找到匹配的处理器
     */
    public const FOUND = 3;

    /**
     * 获取路由调度状态
     *
     * 返回值应该是下面几个值之一：
     * - NOT_FOUND          没找到当前请求的处理器
     * - METHOD_NOT_ALLOWED 找到当前请求的处理器，但是请求方法不允许
     * - FOUND              找到匹配的处理器
     *
     * @return int 状态码
     */
    public function getStatus(): int;

    /**
     * 如果状态码为 FOUND，返回相应的处理器
     *
     * @throws StatusNotMatchException 状态码不匹配
     * @return callable
     */
    public function getHandler(): callable;

    /**
     * 如果状态码为 METHOD_NOT_ALLOWED，返回允许的请求方法列表
     *
     * @throws StatusNotMatchException 状态码不匹配
     * @return array 允许的请求方法列表
     */
    public function getAllowMethods(): array;

    /**
     * 如果状态码为 FOUND ，返回路由中的参数
     *
     * @throws StatusNotMatchException 状态码不匹配
     * @return array 路由参数
     */
    public function getParams(): array;
}
