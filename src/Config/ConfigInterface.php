<?php

declare(strict_types=1);

namespace Lqf\Config;

use \ArrayAccess;
use \Iterator;

/**
 * 配置接口
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
interface ConfigInterface extends ArrayAccess, Iterator
{
    /**
     * 从配置文件加载一个配置合并到现有配置
     *
     * @param  string $filePath 配置文件路径
     *
     * @return void
     */
    public function loadAndMerge($filePath);

    /**
     * 获取一个配置项的值
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null);
    
    /**
     * 设置一个配置项的值
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function set($key, $value);
    
    /**
     * 判断一个配置项是否存在
     *
     * @param  string $key
     *
     * @return boolean
     */
    public function has($key);
    
    /**
     * 获取全部配置
     *
     * @return array
     */
    public function all();
}
