<?php

define('ROOT', __DIR__ . '/../orion/');
define('IN_APP', true);

require_once(ROOT . 'interfase/common.php');

$user->init();
$user->setup();

if (!defined('NO_LOGIN')) {
	if (!$user->is('member')) {
		do_login();
	}
}