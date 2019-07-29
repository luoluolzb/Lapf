<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use lqf\AppFactory;
use lqf\App;
use lqf\Env;
use lqf\route\Route;
use luoluolzb\di\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest as Request;
use Nyholm\Psr7\Response;

// 注入框架依赖
AppFactory::bindEnv(new Env($_SERVER));
AppFactory::bindRoute(new Route);
AppFactory::bindPsr11Container(new Container);
AppFactory::bindPsr17Factory(new Psr17Factory);
$app = AppFactory::create();


// 注入依赖
$container = $app->getContainer();

$container->set('pdo', function (Container $c) {
    return new \PDO(
        "mysql:host=localhost;port=3306;dbname=test;charset=utf8;",
        'root',
        '123456'
    );
});


// 注册路由
$route = $app->getRoute();

$route->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("welcome to use lqf");
    return $response;
});

$route->get('/uri', function (Request $request, Response $response) {
    $body = $response->getBody();
    $body->write('<pre>');
    $body->write(var_export($_SERVER, true));
    $uri = $request->getUri();
    $body->write((string)$uri);
    $body->write('</pre>');
    return $response;
});

$route->any('/request_header', function (Request $request, Response $response) {
    $headers = $request->getHeaders();
    $response->getBody()->write(json_encode($headers));
    return $response;
});

$route->any('/request_body_stream', function (Request $request, Response $response) {
    $stream = $request->getBody();
    var_dump((string)$stream);
    return $response;
});

$route->any('/response', function (Request $request, Response $response) {
    $response = $response->withHeader('Server', 'lqf');
    $body = $response->getBody();
    $body->write("hello");
    return $response;
});

$route->post('/post', function (Request $request, Response $response) {
    $params = $request->getParsedBody();
    $response->getBody()->write(var_export($params, true));
    return $response;
});

$route->any('/hello[/{name:\w+}]', function (Request $request, Response $response) {
    $name = $this->getRouteParam('name', 'lqf');
    $response->getBody()->write("hello, {$name}");
    return $response;
});

$route->get('/users', function (Request $request, Response $response) {
    $pdo = $this->getContainer()->get('pdo');
    $stmt = $pdo->query("select * from tb_user");
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($rows));
    return $response;
});

$route->get('/user/{id:\d+}', function (Request $request, Response $response) {
    $pdo = $this->getContainer()->get('pdo');
    $id = $this->getRouteParam('id', -1);
    $stmt = $pdo->query("select * from tb_user where id = {$id}");
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($row));
    return $response;
});

// 执行应用
$app->start();
