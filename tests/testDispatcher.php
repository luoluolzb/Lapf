<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use lqf\route\Collector as RouteCollector;
use lqf\route\Dispatcher as RouteDispatcher;
use lqf\route\DispatchResult;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17Factory = new Psr17Factory();
$psr17Factory = new Psr17Factory();
$uri = $psr17Factory->createUri('http://localhost/g');
$request = $psr17Factory->createRequest('VIEW', $uri);

$collector = new RouteCollector();
$collector->map('GET', '/', function() {});
$collector->map('POST', '/', function() {});

$dispatcher = new RouteDispatcher($collector);
$result = $dispatcher->dispatch($request);
var_dump($result);
