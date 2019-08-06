<?php

declare(strict_types=1);

namespace Lqf\Route;

/**
 * 中间件提供者
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class MiddlewareResolver
{
    public function __invoke($entry)
    {
        if (\is_string($entry)) {
            return new $entry();
        }
        return $entry;
    }
}
