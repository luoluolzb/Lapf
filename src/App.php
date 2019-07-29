<?php

declare(strict_types=1);

namespace lqf;

use \RuntimeException;
use lqf\route\RouteInterface;
use lqf\route\DispatchResult;
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
     * @var RouteInterface
     */
    private $route;

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
     * 路由参数
     *
     * @var Array
     */
    private $routeParams;
    
    /**
     * 实例化应用类
     */
    public function __construct(
        Env $env,
        RouteInterface $route,
        ContainerInterface $container,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        UploadedFileFactoryInterface $uploadedFileFactory,
        ServerRequestFactoryInterface $serverRequestFactory
    ) {
        $this->env = $env;
        $this->route = $route;
        $this->container = $container;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->serverRequestFactory = $serverRequestFactory;
        $this->routeParams = [];
    }

    /**
     * 获取容器对象
     *
     * @return ContainerInterface 容器对象
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 获取容器对象
     *
     * @return RouteInterface 路由对象
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * 开始执行应用
     *
     * @return void
     */
    public function start(): void
    {
        $request = $this->getRequest();
        $response = $this->dispatch($request);
        $this->sendResponse($response);
    }

    /**
     * 获取全部路由参数
     *
     * @return array 全部路由参数
     */
    public function getRouteParams(): array
    {
        return $this->routeParams ?? [];
    }

    /**
     * 获取一个路由参数
     *
     * @param  string $name    参数名称
     * @param  mixed  $default 参数默认值
     *
     * @return mixed 参数值
     */
    public function getRouteParam(string $name, $default)
    {
        return $this->getRouteParams()[$name] ?? $default;
    }

    /**
     * 构建请求对象
     *
     * @return ServerRequestInterface 服务器请求对象
     */
    protected function getRequest(): ServerRequestInterface
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
        if (!\function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (\substr($name, 0, 5) == 'HTTP_') {
                    $name = \ucwords(\strtolower(\str_replace('_', ' ', \substr($name, 5))));
                    $name = \str_replace(' ', '-', $name);
                    $headers[$name] = $value;
                }
            }
        } else {
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
                parse_str($bodyStr, $parsedBody);
                $request = $request->withParsedBody($parsedBody);
            }
        }
        
        return $request;
    }

    /**
     * 路由调度
     *
     * @param  ServerRequestInterface $request 服务器请求对象
     *
     * @return ResponseInterface 响应对象
     */
    protected function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();

        // 路由调度
        $res = $this->route->dispatch($request);
        switch ($res->getStatusCode()) {
            case DispatchResult::FOUND:
                $handler = $res->getHandler();
                $this->routeParams = $res->getParams();
                if ($handler instanceof \Closure) {
                    $response = $handler->call($this, $request, $response);
                } else {
                    $response = $handler($request, $response);
                }
                if (!($response instanceof ResponseInterface)) {
                    throw new RuntimeException("The handler must be return instance of ResponseInterface");
                }
                break;
            
            case DispatchResult::METHOD_NOT_ALLOWED:
                $allowMethods = $res->getAllowMethods();
                $response = $response->withStatus(405);
                $response = $response->withHeader('Allow', $allowMethods);
                $response = $response->withBody(
                    $this->streamFactory->createStream("<h1>405 Method Not Allowed</h1>")
                );
                break;
            
            case DispatchResult::NOT_FOUND:
                $response = $response->withStatus(404);
                $response = $response->withBody(
                    $this->streamFactory->createStream("<h1>404 Not Found</h1>")
                );
                break;
            
            default:
                break;
        }

        return $response;
    }

    /**
     * 发送响应
     *
     * @param  ResponseInterface $response 响应对象
     *
     * @return void
     */
    protected function sendResponse(ResponseInterface $response): void
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
