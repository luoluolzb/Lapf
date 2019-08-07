<?php

declare(strict_types=1);

namespace Lqf;

use \RuntimeException;

/**
 * 环境类
 *
 * 为了安全考虑，所有的环境参数只能在实例化类的时候一次性导入
 * 在之后不能修改，只能读取
 *
 * 你可以继承此类让它可以从配置文件（如.env）读取参数
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class Env
{
    /**
     * 框架必要的运行环境参数
     */
    public const REQUIRE_PARAMS = [
        'HTTP_HOST',
        'REQUEST_URI',
        'REQUEST_METHOD',
        'SERVER_PROTOCOL',
    ];

    /**
     * 环境参数表
     *
     * @var array
     */
    protected $params;

    /**
     * 实例化环境类
     *
     * @param array $params 环境参数
     */
    public function __construct(array $params)
    {
        $this->params = $params;

        // 检测必要运行环境参数
        foreach (self::REQUIRE_PARAMS as $name) {
            if (!isset($this->params[$name])) {
                throw new RuntimeException("Lack of necessary environment parameter: {$name}");
            }
        }
    }

    /**
     * 获取某个环境参数
     *
     * @param mixed $name 参数名
     *
     * @return mixed 参数值
     */
    public function get($name)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * 判断是否有某个环境参数
     *
     * @param string $name 环境参数名称
     *
     * @return bool 是否存在
     */
    public function has($name): bool
    {
        return isset($this->params[$name]);
    }

    /**
     * 魔术方法：获取某个环境参数
     *
     * @param mixed $name 参数名
     *
     * @return mixed 参数值
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
