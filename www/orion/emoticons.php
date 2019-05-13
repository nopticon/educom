<?php

define('IN_APP', true);
define('NO_A_META', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$comments->emoticons();

page_layout('EMOTICONS', 'emoticons');
