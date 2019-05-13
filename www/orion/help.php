<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$help = new help();
$help->run();

page_layout($help->get_title('HELP'), $help->get_template('help'));
