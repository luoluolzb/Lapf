<?php
declare(strict_types=1);

namespace Lqf;

use \RuntimeException;
use Lqf\Config\Config;
use Lqf\Route\Router;
use Lqf\Config\ConfigInterface;
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
     * @var Router
     */
    private $router;

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
     * 配置对象
     *
     * @var ConfigInterface
     */
    private $config;
    
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

        $this->config = new Config([]);

        $this->router = new Router();
        $this->router->setMethodNotAllowedHandler(function (
            ServerRequestInterface $request,
            array $allowMethods
        ) use (
            $responseFactory,
            $streamFactory
        ) {
            return $responseFactory->createResponse(405)
            ->withHeader('Allow', $allowMethods)
            ->withBody(
                $streamFactory->createStream(
                    <<<'EOS'
                    <title>405 Method Not Allowed</title>
                    <h1 align="center">405 Method Not Allowed</h1><hr />
                    <p align="center">lqf framework<p/>
                    EOS
                )
            );
        });
        $this->router->setNotFoundHandler(function (
            ServerRequestInterface $request
        ) use (
            $responseFactory,
            $streamFactory
        ) {
            return $responseFactory->createResponse(404)
            ->withBody(
                $streamFactory->createStream(
                    <<<'EOS'
                    <title>404 Not Found</title>
                    <h1 align="center">404 Not Found</h1><hr />
                    <p align="center">lqf framework<p/>
                    EOS
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
     * @return Router 路由对象
     */
    public function getRouter(): Router
    {
        return $this->router;
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
     * 获取应用的配置对象
     *
     * @return Config 配置对象
     */
    public function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * 判断当前是否为 debug 模式
     * 相当于 $app->getConfig()->get('debug')
     *
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->config->get('debug');
    }

    /**
     * 开始执行应用
     *
     * @return void
     */
    public function start(): void
    {
        $this->request  = $this->buildRequest();
        $this->response = $this->router->dispatch($this->request);
        $this->sendResponse($this->response);
    }

    /**
     * 构建请求对象
     *
     * @return ServerRequestInterface 服务器请求对象
     */
    private function buildRequest(): ServerRequestInterface
    {
        $serverParams = $this->env->server();

        // 构建请求uri对象
        $requestUri = $serverParams['REQUEST_URI'];
        $protocol = $serverParams['SERVER_PROTOCOL'];
        $scheme = \strtolower(\explode('/', $protocol)[0]);
        $host = $serverParams['HTTP_HOST'];

        $fullRawUri = "{$scheme}://{$host}{$requestUri}";
        $uri = $this->uriFactory->createUri($fullRawUri);

        // 构建请求对象
        $requestMethod = strtoupper($serverParams['REQUEST_METHOD']);
        $request = $this->serverRequestFactory->createServerRequest($requestMethod, $uri, $serverParams);
        \parse_str($uri->getQuery(), $queryParams);
        $request = $request->withQueryParams($queryParams);

        // 设置请求头
        if (!\function_exists('\getallheaders')) {
            $headers = [];
            foreach ($serverParams as $name => $value) {
                if (\substr($name, 0, 5) == 'HTTP_') {
                    $name = \strtolower(\str_replace('_', ' ', \substr($name, 5)));
                    $name = \str_replace(' ', '-', \ucwords($name));
                    $headers[$name] = $value;
                }
            }
        } else {
            $headers = \getallheaders();
        }
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, \explode(',', $value));
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

        // 设置上传文件
        if ($requestMethod === 'POST') {
            $uploadedFiles = [];
            foreach ($this->env->files() as $field => $value) {
                if (\is_array($value['error'])) { // 多个文件
                    foreach ($value['error'] as $i => $error) {
                        $stream = file_exists($value['tmp_name'][$i])
                        ? $this->streamFactory->createStreamFromFile($value['tmp_name'][$i])
                        : $this->streamFactory->createStream();
                        $uploadedFiles[$field][] = $this->uploadedFileFactory->createUploadedFile(
                            $stream,
                            $value['size'][$i],
                            $value['error'][$i],
                            $value['name'][$i],
                            $value['type'][$i]
                        );
                    }
                } else { // 单个文件
                    $stream = file_exists($value['tmp_name'])
                    ? $this->streamFactory->createStreamFromFile($value['tmp_name'])
                    : $this->streamFactory->createStream();
                    $uploadedFiles[$field] = $this->uploadedFileFactory->createUploadedFile(
                        $stream,
                        $value['size'],
                        $value['error'],
                        $value['name'],
                        $value['type']
                    );
                }
            }
            $request = $request->withUploadedFiles($uploadedFiles);
            if (!empty($uploadedFiles)) {
                $request = $request->withParsedBody($_POST);
            }
        }

        // 设置cookie
        $request = $request->withCookieParams($this->env->cookie());
        
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
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                \header(\sprintf('%s: %s', $name, $value), false);
            }
        }

        // 发送响应正文
        $stream = $response->getBody();
        $stream->rewind();
        while (!empty($buffer = $stream->read(2048))) {
            print($buffer);
        }
    }
}
