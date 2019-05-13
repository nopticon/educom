<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$topics = new topics();
$topics->run();

page_layout($topics->get_title(), $topics->get_template());
