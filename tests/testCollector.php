<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lqf\Route\Collector as RouteCollector;

$collector = new RouteCollector();
$collector->map('GET', '/', function() {
});

$collector->map('POST', '/', function() {
});

// throw exception
try {
	$collector->map('GET', '/', function() {
	});
} catch (RuntimeException $e) {
	var_dump($e->getMessage());
}

foreach ($collector as $key => $value) {
	var_dump($value);
}
