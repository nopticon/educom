<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$acp = new acp();
$acp->run();

page_layout($acp->get_title('ACP'), $acp->get_template('acp'));
