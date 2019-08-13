<?php

declare(strict_types=1);

namespace Lqf;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use luoluolzb\di\Container;
use Nyholm\Psr7\Factory\Psr17Factory;

/**
 * 应用创建工厂
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class AppFactory
{
    /**
     * 应用实例
     *
     * @var App
     */
    private static $app = null;

    /**
     * @var Env
     */
    private static $env;

    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * @var UriFactoryInterface
     */
    private static $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    private static $streamFactory;

    /**
     * @var RequestFactoryInterface
     */
    private static $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private static $responseFactory;

    /**
     * @var UploadedFileFactoryInterface
     */
    private static $uploadedFileFactory;

    /**
     * @var ServerRequestFactoryInterface
     */
    private static $serverRequestFactory;

    /**
     * @var mixed
     */
    private static $psr17Factory;

    /**
     * 绑定一个 Env 实例到将要创建的应用对象上
     *
     * @param  Env $env
     *
     * @return void
     */
    public static function bindEnv(Env $env): void
    {
        self::$env = $env;
    }

    /**
     * 绑定一个 psr-11 容器实例到将要创建的应用对象上
     *
     * @param  ContainerInterface $container psr-11容器实例
     *
     * @return void
     */
    public static function bindPsr11Container(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * 绑定一个 psr-17 Request 创建工厂实例到将要创建的应用对象上
     *
     * @param  RequestFactoryInterface $factory
     *
     * @return void
     */
    public static function bindRequestFactory(RequestFactoryInterface $factory): void
    {
        self::$requestFactory = $factory;
    }
    
    /**
     * 绑定一个 psr-17 Response 创建工厂实例到将要创建的应用对象上
     *
     * @param  ResponseFactoryInterface $factory
     *
     * @return void
     */
    public static function bindResponseFactory(ResponseFactoryInterface $factory): void
    {
        self::$responseFactory = $factory;
    }
    
    /**
     * 绑定一个 psr-17 ServerRequest 创建工厂实例到将要创建的应用对象上
     *
     * @param  ServerRequestFactoryInterface $factory
     *
     * @return void
     */
    public static function bindServerRequestFactory(ServerRequestFactoryInterface $factory): void
    {
        self::$serverRequestFactory = $factory;
    }
    
    /**
     * 绑定一个 psr-17 Stream 创建工厂实例到将要创建的应用对象上
     *
     * @param  StreamFactoryInterface $factory
     *
     * @return void
     */
    public static function bindStreamFactory(StreamFactoryInterface $factory): void
    {
        self::$streamFactory = $factory;
    }

    /**
     * 绑定一个 psr-17 UploadedFile 创建工厂实例到将要创建的应用对象上
     *
     * @param  UploadedFileFactoryInterface $factory
     *
     * @return void
     */
    public static function bindUploadedFileFactory(UploadedFileFactoryInterface $factory): void
    {
        self::$uploadedFileFactory = $factory;
    }

    /**
     * 绑定一个 psr-17 Uri 创建工厂实例到将要创建的应用对象上
     *
     * @param  UriFactoryInterface $factory
     *
     * @return void
     */
    public static function bindUriFactoryInterface(UriFactoryInterface $factory): void
    {
        self::$uriFactory = $factory;
    }

    /**
     * 绑定一个实现了所有 psr-17 创建工厂实例到将要创建的应用对象上
     *
     * @param  mixed $psr17Factory
     *
     * @return void
     */
    public static function bindPsr17Factory($psr17Factory): void
    {
        self::$psr17Factory = $psr17Factory;
        self::bindStreamFactory($psr17Factory);
        self::bindRequestFactory($psr17Factory);
        self::bindResponseFactory($psr17Factory);
        self::bindUploadedFileFactory($psr17Factory);
        self::bindUriFactoryInterface($psr17Factory);
        self::bindServerRequestFactory($psr17Factory);
    }

    /**
     * 获取应用实例
     *
     * @return App 每次返回的为同一实例
     */
    public static function getInstance(): App
    {
        if (isset(self::$app)) {
            return self::$app;
        } else {
            if (!isset(self::$env)) {
                self::bindEnv(new Env($_SERVER, $_ENV, $_COOKIE, $_FILES));
            }
            if (!isset(self::$container)) {
                self::bindPsr11Container(new Container());
            }
            if (!isset(self::$psr17Factory)) {
                self::bindPsr17Factory(new Psr17Factory());
            }
            return $app = new App(
                self::$env,
                self::$container,
                self::$uriFactory,
                self::$streamFactory,
                self::$requestFactory,
                self::$responseFactory,
                self::$uploadedFileFactory,
                self::$serverRequestFactory,
            );
        }
    }
}
