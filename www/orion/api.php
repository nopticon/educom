<?php

define('IN_APP', true);
require_once('./interfase/npi/cliws.php');
require_once('./interfase/functions.php');
require_once('./objects/api.php');

// $db = new database();
$db = npi('api');
$ws = npi('http://a/api/');

$ws->__ws_construct('ServiceBus', 'service');

$methods = array(
	'user_authenticate' => 'email password',
	'user_create' => 'email password firstname lastname',
	'user_token' => 'token',
	'user_logout' => 'token',
	'user_read' => 'email',
	'user_attributes' => 'token',
	'user_search' => 'criteria',
	'user_update' => 'email name value',
	'user_delete' => 'email',
	'user_activate_token' => 'token',
	'user_activate_confirm' => 'token password_new password_confirm',
	'user_password' => 'email password_old password_new password_confirm',
	'user_password_reset' => 'email',
	'user_password_token' => 'email token',
	'user_password_confirm' => 'email token password_new password_confirm',
	'user_change' => 'email_old email_new',
	'user_change_confirm' => 'token',

	'user_number_create_direct' => 'email country phone',
	'user_number_create' => 'email country phone',
	'user_number_delete' => 'email country phone',
	'user_number_confirm' => 'email phone verification',
	'user_number_list' => 'email',
	'user_number_attributes' => 'email data',
	'user_block_admin' => 'email',
	'user_unblock_admin' => 'email',
	'user_unblock' => 'email token',
	'user_get_token' => 'email',
	'user_data' => 'email data',
	'user_data_list' => 'email',

	'company_create' => 'name',
	'company_modify' => 'name replace',

	'app_list' => 'company',
	'app_create' => 'company name start end ip',
	'app_modify' => 'company name start end ip',
	'app_secret' => 'company name',

	'app_method' => 'application method',
	'app_method_validate' => 'method',

	'test' => '',
	'system_methods' => ''
);

foreach ($methods as $k => $v) {
	$v = 'config ' . $v;

	$vv = w();
	foreach (w($v) as $a) {
		$vv[$a] = 'xsd:string';
	}

	$ws->__ws_method($k, $vv, array('return' => 'xsd:string'));
}

$ws->__ws_service();
