<?php
require __DIR__ . '/../../../vendor/autoload.php';

use lqf\route\Route;
use lqf\route\DispatchResult;

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $patInfo = substr($uri, 0, $pos);
    $queryStr = substr($uri, $pos + 1);
} else {
    $patInfo = $uri;
    $queryStr = '';
}
$patInfo = rawurldecode($patInfo);

$route = new Route;

$route->get('/', function() {
    echo "route test";
});

// 抛出方法不允许异常
// $route->aaa('/', function() {
//     echo "route test";
// });

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

$dispatchResult = $route->dispatch($httpMethod, $patInfo);

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
