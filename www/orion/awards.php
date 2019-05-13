<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$awards = new awards();
$awards->run();

page_layout('AWARDS', 'awards');
