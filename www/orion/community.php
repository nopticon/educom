<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$community = new community();
$community->run();

page_layout('COMMUNITY', 'community', false, false);
