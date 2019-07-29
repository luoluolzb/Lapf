<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;

$psr17Factory = new Psr17Factory();

var_dump($_SERVER);

$uri = $psr17Factory->createUri($_SERVER['REQUEST_URI']);
var_dump($uri->getPath());
