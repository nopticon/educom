<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$artists = new artists();
$artists->run();

page_layout($artists->get_title('ARTISTS'), $artists->get_template('artists'), false, $artists->ajax());
