<?php

define('IN_APP', true);
require_once('./interfase/common.php');
// require_once(ROOT . 'objects/rss.php');

$user->init();
$user->setup();

$mode = request_var('mode', '');
if (empty($mode)) {
	fatal_error();
}

$rss = new rss();

$method = '_' . $mode;
if (!method_exists($rss, $method)) {
	fatal_error();
}

$rss->smode($mode);
$rss->$method();
$rss->output();
