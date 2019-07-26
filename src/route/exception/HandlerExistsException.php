<?php
namespace lqf\route\exception;

/**
 * 路由处理器已经存在异常类
 *
 * 当尝试添加一个已经存在的路由规则的处理器时抛出
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class HandlerExistsException extends RouteException
{

}
