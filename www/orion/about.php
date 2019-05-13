<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$about = new about();
$about->run();

page_layout('ABOUT', 'about', false, false);
