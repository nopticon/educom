<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$win = new win();
$win->run();

page_layout($win->get_title('WIN'), $win->get_template('win'));
