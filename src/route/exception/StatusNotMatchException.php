<?php
namespace lqf\route\exception;

/**
 * 路由调度状态码不匹配异常类
 *
 * 当尝试从路由调度结果对象中取出不匹配当前状态码的数据时抛出
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class StatusNotMatchException extends RouteException
{

}
