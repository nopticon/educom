<?php

define('IN_APP', true);
require_once('./interfase/common.php');
require_once(ROOT . 'interfase/downloads.php');

$user->init();
$user->setup();

if (!$user->is('member')) {
	do_login();
}

$today = new today();
$today->run();

page_layout('NOTIFICATIONS', 'today');
