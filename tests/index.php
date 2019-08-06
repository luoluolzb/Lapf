<?php

declare(strict_types=1);

namespace tests;

require __DIR__ . '/../vendor/autoload.php';

use Lqf\AppFactory;
use Lqf\Env;
use Lqf\Route\Router;
use luoluolzb\di\Container;

use Nyholm\Psr7\ServerRequest as Request;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Factory\Psr17Factory;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// 自己注册错误处理，框架不接管
$whoops = new \Whoops\Run;
$whoops->appendHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

// 注入框架依赖
AppFactory::bindPsr11Container(new Container);
AppFactory::bindPsr17Factory(new Psr17Factory);
AppFactory::bindEnv(new Env($_SERVER));
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
$router = $app->getRouter();

// -------------- 路由测试 --------------

$router->map('GET', '/', function (Request $request, Response $response): Response {
    $response->getBody()->write("welcome to use lqf");
    return $response;
});

// 抛出路由映射已经存在的异常
try {
    $router->get('/', function (Request $request, Response $response): Response {
        return $response;
    });
} catch (\RuntimeException $e) {
}

$router->any('/hello[/{name:\w+}]', function (Request $request, Response $response, array $params): Response {
    $name = $params['name'] ?? 'lqf';
    $response->getBody()->write("hello, {$name}");
    return $response;
});

// -------------- 测试路由分组 --------------

$router->group('/abc', function (Router $router) {
    $router->get('/ddd', function (Request $request, Response $response): Response {
        $response->getBody()->write('ddd');
        return $response;
    })->get('/eee', function (Request $request, Response $response): Response {
        $response->getBody()->write('eee');
        return $response;
    })->get('/fff', function (Request $request, Response $response): Response {
        $response->getBody()->write('fff');
        return $response;
    });
    $router->group('/ghi', function (Router $router) {
        $router->get('/jjj', function (Request $request, Response $response): Response {
            $response->getBody()->write('jjj');
            return $response;
        })->get('/kkk', function (Request $request, Response $response): Response {
            $response->getBody()->write('kkk');
            return $response;
        });
    });
});

// -------------- 测试操作请求对象 --------------

$router->get('/uri', function (Request $request, Response $response): Response {
    $body = $response->getBody();
    $body->write('<pre>');
    $body->write(var_export($_SERVER, true));
    $uri = $request->getUri();
    $body->write((string)$uri);
    $body->write('</pre>');
    return $response;
});

$router->any('/request_header', function (Request $request, Response $response): Response {
    $headers = $request->getHeaders();
    $response->getBody()->write(\json_encode($headers));
    return $response;
});

$router->any('/request_body_stream', function (Request $request, Response $response): Response {
    $stream = $request->getBody();
    var_dump((string)$stream);
    return $response;
});

// -------------- 测试操作响应对象 --------------

$router->any('/response', function (Request $request, Response $response): Response {
    $response = $response->withHeader('Server', 'lqf');
    $body = $response->getBody();
    $body->write("hello");
    return $response;
});

$router->post('/post', function (Request $request, Response $response): Response {
    $params = $request->getParsedBody();
    $response->getBody()->write(\var_export($params, true));
    return $response;
});

// -------------- 测试路由配合依赖注入容器的实际应用 --------------

$router->get('/users', function (Request $request, Response $response) use ($app): Response {
    $pdo = $app->get('pdo');
    $stmt = $pdo->query('select * from tb_user');
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $response->getBody()->write(\json_encode($rows));
    return $response;
});

$router->get('/user/{id:\d+}', function (Request $request, Response $response, array $params) use ($app): Response {
    $pdo = $app->get('pdo');
    $id = $params['id'] ?? -1;
    $stmt = $pdo->query("select * from tb_user where id = {$id}");
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $response->getBody()->write(\json_encode($row));
    return $response;
});

// -------------- 测试中间件 --------------

// 前置中间件
class BeforeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 先处理自己的
        $request = $request->withAttribute('foo', 'bar');
        $response = new Response();
        $body = $response->getBody();
        $body->write('Before ');
        // 再处理别人的
        $existingContent = (string) $handler->handle($request)->getBody();
        $body->write($existingContent);
        return $response;
    }
}

// 后置中间件
class AfterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 先处理别人的
        $response = $handler->handle($request);
        // 在处理自己的
        $foo = $request->getAttribute('foo');
        $body = $response->getBody();
        $body->write(' After');
        $body->write(" foo={$foo}");
        return $response;
    }
}

// 添加中间件
// $router->middleware(BeforeMiddleware::class);
// $router->middleware(AfterMiddleware::class);

$router->get('/middleware', function (Request $request, Response $response) {
    $response->getBody()->write('Middleware');
    return $response;
});

// -------------- 测试控制器 --------------

class IndexController
{
    public function get(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('IndexController::get');
        return $response;
    }

    public function post(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('IndexController::post');
        return $response;
    }
}

$router->get('/index/get', 'IndexController::get');
$router->get('/index/post', 'IndexController::post');

// 执行应用
$app->start();
