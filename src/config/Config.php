<?php

declare(strict_types=1);

namespace Lqf\Config;

use Noodlehaus\Config as NoodlehausConfig;

/**
 * 配置
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Config extends NoodlehausConfig implements ConfigInterface
{
    /**
     * @see ConfigInterface::loadAndMerge
     */
    public function loadAndMerge($filePath)
    {
        $this->merge(new Config($filePath));
    }
}
