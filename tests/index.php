<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lqf\AppFactory;
use Lqf\Env;
use luoluolzb\di\Container;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest as Request;
use Nyholm\Psr7\Response;

// 自己注册错误处理，框架不接管
$whoops = new Whoops\Run;
$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
$whoops->register();

// 注入框架依赖
AppFactory::bindPsr11Container(new Container);
AppFactory::bindPsr17Factory(new Psr17Factory);
AppFactory::bindEnv(new Env($_SERVER));
$app = AppFactory::create();


// 注入依赖
$container = $app->getContainer();

$container->set('pdo', function (Container $c) {
    return new PDO(
        "mysql:host=localhost;port=3306;dbname=test;charset=utf8;",
        'root',
        '123456'
    );
});


// 注册路由
$route = $app->getRoute();

// 路由测试

$route->map('GET', '/', function (Request $request, Response $response) {
    $response->getBody()->write("welcome to use lqf");
    return $response;
});

// 抛出路由映射已经存在的异常
try {
    $route->get('/', function (Request $request, Response $response) {
        return $response;
    });
} catch (Exception $e) {

}

$route->any('/hello[/{name:\w+}]', function (Request $request, Response $response, array $params) {
    $name = $params['name'] ?? 'lqf';
    $response->getBody()->write("hello, {$name}");
    return $response;
});

// 路由分组
$route->group('/abc', function ($route) {
    $route->get('/ddd', function (Request $request, Response $response) {
        $response->getBody()->write('ddd');
        return $response;
    })->get('/eee', function (Request $request, Response $response) {
        $response->getBody()->write('eee');
        return $response;
    })->get('/fff', function (Request $request, Response $response) {
        $response->getBody()->write('fff');
        return $response;
    });
    $route->group('/ghi', function ($route) {
        $route->get('/jjj', function (Request $request, Response $response) {
            $response->getBody()->write('jjj');
            return $response;
        })->get('/kkk', function (Request $request, Response $response) {
            $response->getBody()->write('kkk');
            return $response;
        });
    });
});

// 测试操作请求对象

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

// 测试操作响应对象

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

// 测试路由配合依赖注入容器的实际应用

$route->get('/users', function (Request $request, Response $response) use ($app) {
    $pdo = $app->get('pdo');
    $stmt = $pdo->query("select * from tb_user");
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($rows));
    return $response;
});

$route->get('/user/{id:\d+}', function (Request $request, Response $response, array $params) use ($app) {
    $pdo = $app->get('pdo');
    $id = $params['id'] ?? -1;
    $stmt = $pdo->query("select * from tb_user where id = {$id}");
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($row));
    return $response;
});

// 执行应用
$app->start();
