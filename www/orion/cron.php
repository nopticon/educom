<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init(false, true);

$module = request_var('module', '');

if (!empty($module) && preg_match('#^([a-z\_]+)$#i', $module)) {
	$module_path = ROOT . 'objects/cron/' . $module . '.php';

	if (@file_exists($module_path)) {
		$user->setup();

		@require_once($module_path);
		return;
	}
}

$file_content = @file('./template/exceptions/missing.htm');

$matches = array(
	'<!--#echo var="HTTP_HOST" -->' => v_server('HTTP_HOST'),
	'<!--#echo var="REQUEST_URI" -->' => v_server('REQUEST_URI')
);

$orig = $repl = w();

foreach ($matches as $row_k => $row_v) {
	$orig[] = $row_k;
	$repl[] = $row_v;
}

echo str_replace($orig, $repl, implode('', $file_content));
exit;
