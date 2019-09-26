<?php

declare(strict_types=1);

/**
 * 应用配置
 *
 * @author luoluolzb <luoluolzb@163.com>
 */
return [
	// 调试模式开关
	'debug' => true,

	// 数据库配置
	'database' => [
		'type' => 'mysql',
		'host' => 'localhost',
		'port' => '3306',
		'dbname' => 'test',
		'charset' => 'utf8',
		'user' => 'root',
		'password' => '123456',
	],

    'upload_directory' => __DIR__ . '/upload',
];
