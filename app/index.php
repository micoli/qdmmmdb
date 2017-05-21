<?php
// php -S 0.0.0.0:8084 -t static app/index.php
if (php_sapi_name() === 'cli-server' && is_file(__DIR__.'/../static'.preg_replace('#(\?.*)$#','', $_SERVER['REQUEST_URI']))) {
	return false;
}
$app = include __DIR__ . '/bootstrap.php';
$app->run();