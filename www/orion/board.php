<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$board = new board();
$board->run();

page_layout('FORUM_INDEX', 'board');
