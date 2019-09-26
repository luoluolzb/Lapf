<?php

declare(strict_types=1);

namespace tests;

require __DIR__ . '/../vendor/autoload.php';

use Lqf\AppFactory;
use Lqf\Route\Collector;
use luoluolzb\di\Container;
use Nyholm\Psr7\ServerRequest as Request;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

$app = AppFactory::getInstance();

// 加载配置文件
$config = $app->getConfig();
$config->loadAndMerge(__DIR__ . '/config.php');

// 自己注册错误处理，框架不接管
$whoops = new \Whoops\Run;
if ($app->isDebug()) {  // 调试模式
    $whoops->appendHandler(new \Whoops\Handler\PrettyPageHandler);
} else {  // 生产模式
    // 应该添加 Whoops\Handler\CallbackHandler
    // 注入自定义的处理器：如写入错误信息到日志或发送邮件等
    $callbackHandler = new \Whoops\Handler\CallbackHandler(function (\Exception $exception, $inspector, $run) {
        error_log($exception->getMessage());
    });
    $whoops->appendHandler($callbackHandler);
}
$whoops->register();

// 注入依赖
$container = $app->getContainer();

$container->set('pdo', function (Container $c) use ($config) {
    $c = $config->get('database');
    return new \PDO(
        "{$c['type']}:host={$c['host']};port={$c['port']};dbname={$c['dbname']};charset={$c['charset']};",
        $c['user'],
        $c['password']
    );
});

// 注册路由
$router = $app->getRouter();

// -------------- 路由测试 --------------

$router->map('GET', '/', function (Request $request): Response {
    $response = new Response();
    $response->getBody()->write("welcome to use Lqf");
    return $response;
});

// 重复注册路由规则
// 会抛出 RuntimeException 异常
// 可以将 try-catch 注释掉查看效果
try {
    $router->get('/', function (Request $request): Response {
        return new Response();
    });
 } catch (\RuntimeException $e) {
 }

$router->any('/hello[/{name:\w+}]', function (Request $request, array $params): Response {
    $name = $params['name'] ?? 'world';
    $response = new Response();
    $response->getBody()->write("hello, {$name}!");
    return $response;
});

// -------------- 测试路由分组 --------------

$router->group('/abc', function (Collector $collector) {
    $collector->get('/ddd', function (Request $request): Response {
        $response = new Response();
        $response->getBody()->write('ddd');
        return $response;
    })->get('/eee', function (Request $request): Response {
        $response = new Response();
        $response->getBody()->write('eee');
        return $response;
    })->get('/fff', function (Request $request): Response {
        $response = new Response();
        $response->getBody()->write('fff');
        return $response;
    })->group('/ghi', function (Collector $collector) {
        $collector->get('/jjj', function (Request $request): Response {
            $response = new Response();
            $response->getBody()->write('jjj');
            return $response;
        })->get('/kkk', function (Request $request): Response {
            $response = new Response();
            $response->getBody()->write('kkk');
            return $response;
        });
    });
});

// -------------- 测试操作请求对象 --------------

$router->get('/uri', function (Request $request): Response {
    $response = new Response();
    $body = $response->getBody();
    $body->write((string)$request->getUri());
    return $response;
});

$router->get('/request_headers', function (Request $request): Response {
    $response = new Response();
    $headers = $request->getHeaders();
    $response->getBody()->write(\json_encode($headers));
    return $response;
});

$router->get('/request_header', function (Request $request): Response {
    $response = new Response();
    $header = $request->getHeader('User-Agent');
    $response->getBody()->write(\json_encode($header));
    return $response;
});

$router->get('/request_header_line', function (Request $request): Response {
    $response = new Response();
    $headerLine = $request->getHeaderLine('User-Agent');
    $response->getBody()->write($headerLine);
    return $response;
});

$router->any('/request_body_stream', function (Request $request): Response {
    $response = new Response();
    $response->getBody()->write((string)$request->getBody());
    return $response;
});

$router->get('/query_params', function (Request $request): Response {
    $response = new Response();
    $queryParams = $request->getQueryParams();
    $response->getBody()->write(\json_encode($queryParams));
    return $response;
});

// -------------- 测试操作响应对象 --------------

$router->get('/response', function (Request $request): Response {
    $response = new Response();
    $response->getBody()->write($response->getStatusCode() . ' ' . $response->getReasonPhrase());
    return $response->withHeader('framework', 'Lqf');
});

$router->any('/body_params', function (Request $request): Response {
    $response = new Response();
    $params = $request->getParsedBody();
    $response->getBody()->write(\var_export($params, true));
    return $response;
});

// -------------- 测试路由配合依赖注入容器的实际应用 --------------

$router->get('/users', function (Request $request) use ($container): Response {
    $stmt = $container->get('pdo')->query('select * from tb_user');
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $response = new Response();
    $response->getBody()->write(\json_encode($rows));
    return $response;
});

$router->get('/user/{id:\d+}', function (Request $request, array $params) use ($container): Response {
    $id = $params['id'] ?? -1;
    $stmt = $container->get('pdo')->query("select * from tb_user where id = {$id}");
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    $response = new Response();
    $response->getBody()->write(\json_encode($row));
    return $response;
});

// -------------- 测试中间件 --------------

// 前置中间件
class BeforeMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/middleware') {
            // 先处理自己的
            $request = $request->withAttribute('foo', 'bar');
            $response = new Response();
            $body = $response->getBody();
            $body->write('Before ');
            // 再处理别人的
            $existingContent = (string) $handler->handle($request)->getBody();
            $body->write($existingContent);
            return $response;
        } else {
            return $handler->handle($request);
        }
    }
}

// 后置中间件
class AfterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === '/middleware') {
            // 先处理别人的
            $response = $handler->handle($request);
            // 在处理自己的
            $foo = $request->getAttribute('foo');
            $body = $response->getBody();
            $body->write(' After');
            $body->write(" foo={$foo}");
            return $response;
        } else {
            return $handler->handle($request);
        }
    }
}

// 添加中间件
$router->middleware(BeforeMiddleware::class);
$router->middleware(AfterMiddleware::class);

$router->get('/middleware', function (Request $request): Response {
    $response = new Response();
    $response->getBody()->write('Middleware');
    return $response;
});

// -------------- 测试控制器 --------------

class IndexController
{
    public function get(Request $request): Response
    {
        $response = new Response();
        $response->getBody()->write('IndexController::get');
        return $response;
    }

    public function post(Request $request): Response
    {
        $response = new Response();
        $response->getBody()->write('IndexController::post');
        return $response;
    }
}

$router->get('/index/get', 'tests\IndexController::get');
$router->post('/index/post', 'tests\IndexController::post');

// -------------- 测试配置 --------------

$router->get('/config/all', function (Request $request) use ($config) {
    $response = new Response();
    $response->getBody()->write(\json_encode($config->all()));
    return $response;
});

$router->get('/config/database', function (Request $request) use ($config) {
    $response = new Response();
    $response->getBody()->write(\json_encode($config->get('database')));
    return $response; 
});

$router->get('/config/dbname', function (Request $request) use ($config) {
    $response = new Response();
    $response->getBody()->write(\json_encode($config->get('database.dbname')));
    return $response; 
});

// -------------- 测试文件上传 --------------

/**
 * 将上传的文件上传目录和给它分配一个唯一的名称,以避免覆盖现有的上传文件
 *
 * @param string                $directory 上传文件保存目录
 * @param UploadedFileInterface $uploaded  上传文件实例
 * @return string 保存文件名
 */
function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile)
{
    $extension = \pathinfo($uploadedFile->getClientFilename(), \PATHINFO_EXTENSION);
    $basename = \bin2hex(\random_bytes(8));
    $filename = \sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . \DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

$router->post('/fileupload', function (Request $request) use ($config) {
    $directory = $config->get('upload_directory');
    $uploadedFiles = $request->getUploadedFiles();
    $response = new Response();
    $body = $response->getBody();

    // 处理单输入单文件上传
    $uploadedFile = $uploadedFiles['example1'];
    if ($uploadedFile->getError() === \UPLOAD_ERR_OK) {
        $filename = moveUploadedFile($directory, $uploadedFile);
        $body->write('uploaded ' . $filename . '<br/>');
    }

    // 处理多个input的name相同的文件上传
    foreach ($uploadedFiles['example2'] as $uploadedFile) {
        if ($uploadedFile->getError() === \UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $body->write('uploaded ' . $filename . '<br/>');
        }
    }

    // 处理单个带有多文件上传的input
    foreach ($uploadedFiles['example3'] as $uploadedFile) {
        if ($uploadedFile->getError() === \UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
            $body->write('uploaded ' . $filename . '<br/>');
        }
    }

    return $response;
});

// ----------------------------------------

// 执行应用
$app->start();
