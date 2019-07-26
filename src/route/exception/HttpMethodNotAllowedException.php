<?php
namespace lqf\route\exception;

/**
 * http请求方法不被允许异常类
 *
 * 当尝试添加一个不被允许的请求方法的路由规则时抛出
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class HttpMethodNotAllowedException extends RouteException
{
    
}
