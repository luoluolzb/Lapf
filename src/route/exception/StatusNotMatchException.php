<?php
namespace lqf\route\exception;

/**
 * 当尝试从路由调度结果对象中取出不匹配当前状态码的信息时抛出此异常
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class StatusNotMatchException extends RouteException
{

}
