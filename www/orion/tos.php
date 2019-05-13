<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

page_layout('PRIVACY_POLICY', 'tos');
