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

/**
 * 应用创建工厂
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
class AppFactory
{
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

    public static function bindEnv(Env $env): void
    {
        self::$env = $env;
    }

    public static function bindPsr11Container(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function bindRequestFactory(RequestFactoryInterface $factory): void
    {
        self::$requestFactory = $factory;
    }

    public static function bindResponseFactory(ResponseFactoryInterface $factory): void
    {
        self::$responseFactory = $factory;
    }

    public static function bindServerRequestFactory(ServerRequestFactoryInterface $factory): void
    {
        self::$serverRequestFactory = $factory;
    }
    
    public static function bindStreamFactory(StreamFactoryInterface $factory): void
    {
        self::$streamFactory = $factory;
    }

    public static function bindUploadedFileFactory(UploadedFileFactoryInterface $factory): void
    {
        self::$uploadedFileFactory = $factory;
    }

    public static function bindUriFactoryInterface(UriFactoryInterface $factory): void
    {
        self::$uriFactory = $factory;
    }

    public static function bindPsr17Factory($psr17Factory): void
    {
        self::bindStreamFactory($psr17Factory);
        self::bindRequestFactory($psr17Factory);
        self::bindResponseFactory($psr17Factory);
        self::bindUploadedFileFactory($psr17Factory);
        self::bindUriFactoryInterface($psr17Factory);
        self::bindServerRequestFactory($psr17Factory);
    }

    public static function create(): App
    {
        return new App(
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
