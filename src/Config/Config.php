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
     * 框架默认配置
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            // 调试模式开关
            'debug' => false,
        ];
    }

    /**
     * @see ConfigInterface::loadAndMerge
     */
    public function loadAndMerge($filePath): ConfigInterface
    {
        $this->merge(new NoodlehausConfig($filePath));
        return $this;
    }
}
