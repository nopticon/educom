<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$events = new events();
$events->run();

page_layout($events->get_title('EVENTS'), $events->get_template('events'));
