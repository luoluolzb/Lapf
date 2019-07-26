<?php
require __DIR__ . '/../../vendor/autoload.php';

use lqf\env\Env;
use lqf\env\EnvException;

try {
    // CLI 模式下抛出异常
    $env = new Env($_SERVER);

    var_dump($env->REQUEST_METHOD);
    var_dump($env->REQUEST_URI);

    var_dump($env);
} catch (EnvException $e) {
    var_dump($e->getMessage());
}
