<?php

define('IN_APP', true);
require_once('./interfase/common.php');
require_once(ROOT . 'interfase/downloads.php');
require_once(ROOT . 'objects/today.php');

$user->init();
$user->setup();

if (!$user->is('member')) {
	do_login();
}

$today = new today();

$element = request_var('element', 0);
$object = request_var('object', 0);

$select = request_var('select', array(0 => 0));
$select_all = request_var('select_all', 0);

if ($select_all) {
	$today->clear_all();
}

if (count($select)) {
	$delete = request_var('delete', array(0 => 0));

	foreach ($select as $select_element => $void) {
		if (isset($delete[$select_element])) {
			$user->delete_unread($element, $select[$select_element]);
			break;
		}
	}
}

if (!$today->run()) {
	_style('objects_empty');
}

page_layout('UNREAD_ITEMS', 'unread');
