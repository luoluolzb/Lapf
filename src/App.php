<?php
namespace lqf;

use lqf\env\Env;
use lqf\route\RouteInterface;
use Psr\Container\ContainerInterface;
use lqf\route\DispatchResultInterface;

/**
 * 应用类
 */
class App
{
    /**
     * 路由实例
     *
     * @var Env
     */
    protected $env;
    
    /**
     * 路由实例
     *
     * @var RouteInterface
     */
    protected $route;

    /**
     * psr-11容器实例
     *
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * 实例化应用类
     *
     * @param ContainerInterface $container psr-11容器
     * @param RouteInterface     $route     路由实例
     */
    public function __construct(
        Env $env,
        RouteInterface $route,
        ContainerInterface $container
    ) {
        $this->env = $env;
        $this->route = $route;
        $this->container = $container;
    }

    /**
     * 获取环境对象
     *
     * @return Env
     */
    public function getEnv(): Env
    {
        return $this->env;
    }

    /**
     * 获取路由对象
     *
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * 获取容器对象
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 开始执行应用
     *
     * @return void
     */
    public function start()
    {
        // 解析请求
        $httpMethod = $this->env->get('REQUEST_METHOD');
        $uri = $this->env->get('REQUEST_URI');
        if (false !== $pos = strpos($uri, '?')) {
            $pathinfo = substr($uri, 0, $pos);
            $queryStr = substr($uri, $pos + 1);
        } else {
            $pathinfo = $uri;
            $queryStr = '';
        }
        $pathinfo = rawurldecode($pathinfo);

        // 路由调度
        $dispatchResult = $this->route->dispatch($httpMethod, $pathinfo);

        switch ($dispatchResult->getStatus()) {
            case DispatchResultInterface::FOUND:
                $handler = $dispatchResult->getHandler();
                $params = $dispatchResult->getParams();
                $handler($params);
                break;
            
            case DispatchResultInterface::METHOD_NOT_ALLOWED:
                http_response_code(405);
                $allowMethods = $dispatchResult->getAllowMethods();
                header("Allow:" . implode(', ', $allowMethods));
                echo "405 Method Not Allowed";
                break;
            
            case DispatchResultInterface::NOT_FOUND:
                http_response_code(404);
                echo "404 Not Found";
                break;
            
            default:
                break;
        }
    }
}
