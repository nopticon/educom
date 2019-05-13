<?php

define('IN_APP', true);
require_once('./interfase/common.php');

echo $config->git_push_time;

set_config('git_push_time', time());

echo ' / ' . $config->git_push_time;
