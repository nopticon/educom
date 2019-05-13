<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$home = new home();
$home->news();

page_layout('HOME', 'home');
