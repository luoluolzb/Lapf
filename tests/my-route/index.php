<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use lqf\route\Route;
use lqf\route\DispatchResult;
use Nyholm\Psr7\Factory\Psr17Factory;

$psr17Factory = new Psr17Factory();
$uri = $psr17Factory->createUri($_SERVER['REQUEST_URI']);
$request = $psr17Factory->createRequest($_SERVER['REQUEST_METHOD'], $uri);

$route = new Route();

// $route->get('/', function() {
//     echo "route test";
// });

// 抛出方法不允许异常
$route->get('/', function() {
    echo "route test";
});

$route->any('/hello[/{name:\w+}]', function (array $params) {
    echo "hello, ", $params['name'] ?? 'route';
});

$route->group('/admin', function (Route $r) {
    $r->get('/do-something', function () {
        echo "do-something";
    })->get('/do-another-thing', function () {
        echo "do-another-thing";
    })->get('/do-something-else', function () {
        echo "do-something-else";
    });
});

$route->get('/env', function () {
    var_dump($_SERVER);
});

$dispatchResult = $route->dispatch($request);

switch ($dispatchResult->getStatusCode()) {
    case DispatchResult::FOUND:
        $handler = $dispatchResult->getHandler();
        $params = $dispatchResult->getParams();
        $handler($params);
        break;
    
    case DispatchResult::METHOD_NOT_ALLOWED:
        http_response_code(405);
        $allowMethods = $dispatchResult->getAllowMethods();
        header("Allow:" . implode(', ', $allowMethods));
        echo "405 Method Not Allowed";
        break;
    
    case DispatchResult::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found";
        break;
    
    default:
        break;
}
