<?php
require __DIR__ . '/../vendor/autoload.php';

use lqf\env\Env;

// CLI 模式下抛出异常
$env = new Env($_SERVER);

// print_r($env);

var_dump($env->has('REQUEST_URI'));
var_dump($env->get('REQUEST_URI'));
