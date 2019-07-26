<?php
require __DIR__ . '/../vendor/autoload.php';

use lqf\App;
use lqf\env\Env;
use lqf\route\Route;
use luoluolzb\di\Container;

// 注册路由
$route = new Route();

$route->get('/', function () {
    echo "welcome to use lqf";
});

$route->any('/hello[/{name:\w+}]', function (array $params) {
    echo "hello, ", $params['name'] ?? 'lqf';
});


// 注册依赖
$container = new Container();


// 执行应用
$app = new App(new Env($_SERVER), $route, $container);
$app->start();
