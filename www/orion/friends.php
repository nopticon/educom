<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$friends = new friends();
$friends->run();

page_layout($friends->get_title('PARTNERS'), $friends->get_template('partners'));
