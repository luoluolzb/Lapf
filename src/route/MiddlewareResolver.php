<?php

declare(strict_types=1);

namespace Lqf\Route;

/**
 * 中间件提供者（用来给 Relay 获取中间件对象）
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class MiddlewareResolver
{
    public function __invoke($entry)
    {
        if (\is_string($entry)) {
            return new $entry();
        } else {
            return $entry;
        }
    }
}
