<?php
require __DIR__ . '/../../../vendor/autoload.php';

use lqf\route\{Route, DispatchResult};

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $pathinfo = substr($uri, 0, $pos);
    $queryStr = substr($uri, $pos + 1);
} else {
    $pathinfo = $uri;
    $queryStr = '';
}
$pathinfo = rawurldecode($pathinfo);

$route = new Route;

$route->get('/', function() {
    echo "route test";
});

$route->any('/hello/{name:\w+}', function(array $params) {
    echo "hello, ", $params['name'];
});

$route->group('/admin', function (Route $r) {
    $r->add('GET', '/do-something', function() {
        echo "do-something";
    });
    
    $r->add('GET', '/do-another-thing', function() {
        echo "do-another-thing";
    });
    
    $r->add('GET', '/do-something-else', function() {
        echo "do-something-else";
    });
});

$route->get('/env', function(array $params) {
    var_dump($_SERVER);
});

$dispatchResult = $route->dispatch($httpMethod, $pathinfo);

switch ($dispatchResult->getStatus()) {
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
