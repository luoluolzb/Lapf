<?php
namespace lqf\route\exception;

/**
 * 当尝试添加一个已经存在的路由规则(请求方法，路由匹配模式)的处理器时抛出此异常
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class HandlerExistsException extends RouteException
{

}
