<?php
declare(strict_types=1);

namespace tests;

require __DIR__ . '/../vendor/autoload.php';

use Lqf\Route\Collector as RouteCollector;

$collector = new RouteCollector();
$collector->map('GET', '/', function () {});
$collector->map('POST', '/', function () {});
// 重复注册同样的路由会覆盖
$collector->map('GET', '/', function () {});

// 可遍历
foreach ($collector as $key => $value) {
    var_dump($value);
}
