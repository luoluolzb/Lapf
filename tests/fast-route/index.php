<?php

declare(strict_types=1);

namespace tests;

require __DIR__ . '/../../vendor/autoload.php';

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $pathInfo = substr($uri, 0, $pos);
    $queryStr = substr($uri, $pos + 1);
} else {
    $pathInfo = $uri;
    $queryStr = '';
}
$pathInfo = rawurldecode($pathInfo);

var_dump([
    'method'   => $httpMethod,
    'pathInfo' => $pathInfo,
    'queryStr' => $queryStr,
]);

// 添加路由
$routeAdd = function (RouteCollector $r) {
    $r->addRoute('GET', '/users', 'get_all_users_handler');
    // $r->addRoute('GET', '/users', 'get_all_users_handler2');
    // {id} must be a number (\d+)
    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
    // The /{title} suffix is optional
    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
    // 路由分组
    $r->addGroup('/admin', function (RouteCollector $r) {
        $r->addRoute('GET', '/do-something', 'do-something');
        $r->addRoute('GET', '/do-another-thing', 'do-another-thing');
        $r->addRoute('GET', '/do-something-else', 'do-something-else');
    });
};

$dispatcher = simpleDispatcher($routeAdd, [
    'cacheFile' => __DIR__ . '/route.cache', /* required */
    'cacheDisabled' => false,     /* optional, enabled by default */
]);

//不能重复添加
//$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
//    $r->addRoute('GET', '/aaa', 'aaa');
//});

// 路由调度
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    
    case Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        var_dump($allowedMethods);
        break;
    
    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        // ... call $handler with $vars
        var_dump($handler);
        var_dump($vars);
        break;
}
