<?php
declare(strict_types=1);

namespace tests;

require __DIR__ . '/../vendor/autoload.php';

use Lqf\Route\Collector as RouteCollector;
use Lqf\Route\Dispatcher as RouteDispatcher;
use Nyholm\Psr7\Factory\Psr17Factory;

class MyClass
{
    public function __invoke()
    {
        echo "string";
    }
}

$psr17Factory = new Psr17Factory();
$uri = $psr17Factory->createUri('http://localhost/');
$request = $psr17Factory->createRequest('GET', $uri);

$collector = new RouteCollector();
$collector->map('GET', '/', new MyClass);
$collector->map('POST', '/', function () {});

$dispatcher = new RouteDispatcher($collector);
$result = $dispatcher->dispatch($request);
\var_dump($result);
