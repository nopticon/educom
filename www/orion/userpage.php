<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$userpage = new userpage();
$userpage->run();

page_layout($userpage->get_title(), $userpage->get_template());
