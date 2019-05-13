<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$news = new news();
$news->run();

page_layout($news->get_title('NEWS'), $news->get_template('news'));
