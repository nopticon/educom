<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$broadcast = new broadcast();
$broadcast->run();

page_layout($broadcast->get_title('BROADCAST'), $broadcast->get_template('broadcast'));
