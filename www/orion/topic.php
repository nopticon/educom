<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$topic = new topic();
$topic->run();

page_layout($topic->get_title(), $topic->get_template());
