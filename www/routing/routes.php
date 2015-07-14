<?php

$app['router']->get('/', 'HomeController@start');

$app['router']->get('assets/{filename}.{extension}', 'AssetsController@deliver')->where([
	'filename' => '\w+',
	'extension' => 'css|js'
]);

$app['router']->get('{controller}', function($controller) {
	$controllerName = ucfirst($controller) . 'Controller';
	$controller = new $controllerName;
	return $controller->start();
})->where('controller', 'board|emoticons|comments|search|ssv|tos|acp|help|today|events|news');

$app['router']->get('{controller}/{module}/{args?}', function($controller, $module, $args) {
	$_REQUEST['module'] = $module;
	$_REQUEST['args'] = $args;

	$controllerName = ucfirst($controller) . 'Controller';
	$controller = new $controllerName;
	
	return $controller->start($module, $args);
})->where([
	'controller', 'acp|async|cron|news',
	'module' => '[a-z\_]+',
	'args' => '[0-9a-z\_\.\-\:]+'
]);

$app['router']->get('sign{mode}/{code?}', function($mode, $code) {
	$_REQUEST['mode'] = $mode;
	$_REQUEST['code'] = $code;

	do_login();
})->where([
	'mode' => 'in|out|up|r',
	'code' => '[a-z0-9]+'
]);

$app['router']->get('post/{post_id}/{reply?}', function($post_id, $reply) {
	$_REQUEST['p'] = $post_id;

	if ($reply) {
		$_REQUEST['reply'] = 1;
	}

	$controller = 'Topic';
	$controllerName = ucfirst($controller) . 'Controller';
	$controller = new $controllerName;
	
	return $controller->start($module, $args);
});

// RewriteRule ^(sign)(up|r)/([a-z0-9]+)/$ orion/$1.php?mode=$2&code=$3 [nc]