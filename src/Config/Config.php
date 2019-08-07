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
     * 从配置文件加载一个配置合并到现有配置
     *
     * @param  string $filePath 配置文件路径
     *
     * @return void
     */
    public function loadAndMerge($filePath)
    {
        $this->merge(new Config($filePath));
    }
}
