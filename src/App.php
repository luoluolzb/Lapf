<?php

declare(strict_types=1);

namespace lqf;

use \RuntimeException;
use lqf\route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;

/**
 * 应用类
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class App
{
    /**
     * @var Env
     */
    private $env;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var UploadedFileFactoryInterface
     */
    private $uploadedFileFactory;

    /**
     * @var ServerRequestFactoryInterface
     */
    private $serverRequestFactory;

    /**
     * @var Route
     */
    private $route;

    /**
     * 一次请求的服务器请求对象
     *
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * 一次请求的响应对象
     *
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * 实例化应用类
     */
    public function __construct(
        Env $env,
        ContainerInterface $container,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        ServerRequestFactoryInterface $serverRequestFactory
    ) {
        $this->env = $env;
        $this->container = $container;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->serverRequestFactory = $serverRequestFactory;

        $this->route = new Route();
        
        $this->route->setMethodNotAllowedHandler(function (
            RequestInterface $request,
            ResponseInterface $response,
            array $allowMethods
        ) use ($streamFactory) {
            return $response->withBody(
                $streamFactory->createStream(
                    '<title>405 Method Not Allowed</title>
                    <h1 align="center">405 Method Not Allowed</h1><hr />
                    <p align="center">lqf framework<p/>'
                )
            );
        });

        $this->route->setNotFoundHandler(function (
            RequestInterface $request,
            ResponseInterface $response
        ) use ($streamFactory) {
            return $response->withBody(
                $streamFactory->createStream(
                    '<title>404 Not Found</title>
                    <h1 align="center">404 Not Found</h1><hr />
                    <p align="center">lqf framework<p/>'
                )
            );
        });
    }

    /**
     * 获取应用的容器对象
     *
     * @return ContainerInterface 容器对象
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 获取应用的路由对象
     *
     * @return Route 路由对象
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * 获取应用的环境对象
     *
     * @return Env 环境对象
     */
    public function getEnv(): Env
    {
        return $this->env;
    }

    /**
     * 从应用容器取出一个实体
     *
     * @param  mixed $id 实体标识符
     *
     * @return mixed 实体
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * 判断应用容器是否有某个实体
     *
     * @param  mixed $id 实体标识符
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * 魔术方法：从应用容器取出一个实体
     *
     * @param  mixed $id 实体标识符
     *
     * @return mixed 实体
     */
    public function __get($id)
    {
        return $this->container->get($id);
    }

    /**
     * 开始执行应用
     *
     * @return void
     */
    public function start(): void
    {
        $this->request = $this->getRequest();
        $this->response = $this->route->dispatch(
            $this->request,
            $this->responseFactory->createResponse()
        );
        $this->sendResponse($this->response);
    }

    /**
     * 构建请求对象
     *
     * @return ServerRequestInterface 服务器请求对象
     */
    private function getRequest(): ServerRequestInterface
    {
        // 构建请求uri对象
        $requestUri = $this->env->get('REQUEST_URI');
        $protocol = $this->env->get('SERVER_PROTOCOL');
        $scheme = \strtolower(\explode('/', $protocol)[0]);
        $host = $this->env->get('HTTP_HOST');
        $port = $this->env->get('SERVER_PORT');

        $fullRawUri = "{$scheme}://{$host}:{$port}{$requestUri}";
        $uri = $this->uriFactory->createUri($fullRawUri);

        // 构建请求对象
        $requestMethod = $this->env->get('REQUEST_METHOD');
        $request = $this->serverRequestFactory->createServerRequest($requestMethod, $uri);

        // 设置请求头
        if (!\function_exists('getallheaders')) { // apache
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (\substr($name, 0, 5) == 'HTTP_') {
                    $name = \ucwords(\strtolower(\str_replace('_', ' ', \substr($name, 5))));
                    $name = \str_replace(' ', '-', $name);
                    $headers[$name] = $value;
                }
            }
        } else {  // nginx
            $headers = \getallheaders();
        }
        foreach ($headers as $name => &$value) {
            $request = $request->withHeader($name, $value);
        }

        // 构建请求正文流对象
        $resource = \fopen('php://input', 'r');
        if ($resource) {
            $bodyStream = $this->streamFactory->createStreamFromResource($resource);
            $request = $request->withBody($bodyStream);

            // 解析正文参数
            $bodyStr = (string) $bodyStream;
            if ($bodyStr) {
                \parse_str($bodyStr, $parsedBody);
                $request = $request->withParsedBody($parsedBody);
            }
        }
        
        return $request;
    }

    /**
     * 发送响应
     *
     * @param  ResponseInterface $response 响应对象
     *
     * @return void
     */
    private function sendResponse(ResponseInterface $response): void
    {
        // 发送响应码
        \http_response_code($response->getStatusCode());

        // 发送响应头
        foreach ($response->getHeaders() as $name => &$values) {
            foreach ($values as $value) {
                \header(\sprintf('%s: %s', $name, $value), false);
            }
        }

        // 发送响应正文
        $stream = $response->getBody();
        $stream->rewind();
        while (!empty($buffer = $stream->read(2048))) {
            echo $buffer;
        }
    }
}
