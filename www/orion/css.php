<?php

define('IN_APP', true);

if (!defined('ROOT')) {
	define('ROOT', './');
}

require_once(ROOT . 'interfase/common.php');

$user->init(false);
$user->setup();

$filename = request_var('filename', '');
if (empty($filename) || !preg_match('#[a-z\_]+#i', $filename)) {
	fatal_error();
}

$filepath = ROOT . 'template/css/' . $filename . '.css';
if (!@file_exists($filepath)) {
	fatal_error();
}

// 304 Not modified response header
$last_modified = filemtime($filepath);
$f_last_modified = gmdate('D, d M Y H:i:s', $last_modified) . ' GMT';

$http_if_none_match = v_server('HTTP_IF_NONE_MATCH');
$http_if_modified_since = v_server('HTTP_IF_MODIFIED_SINCE');

$etag_server = etag($filepath);
$etag_client = str_replace('-gzip', '', $http_if_none_match);

header('Last-Modified: ' . $f_last_modified);
header('ETag: ' . $etag_server);

if ($etag_client == $etag_server && $f_last_modified == $http_if_modified_since) {
	header('HTTP/1.0 304 Not Modified');
	header('Content-Length: 0');
	exit;
}

$is_firefox = (strstr($user->browser, 'Gecko')) ? true : false;
$is_ie = (strstr($user->browser, 'IE')) ? true : false;

if (strstr($user->browser, 'compatible') || $is_firefox) {
	ob_start('ob_gzhandler');
}

// Headers
#header('Cache-Control: private, no-cache="set-cookie", pre-check=0, post-check=0');
#header('Pragma: no-cache');
#header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 60) . ' GMT');
header('Content-type: text/css; charset=utf-8');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 30)) . ' GMT');

v_style(array(
	'FF' => $is_firefox,
	'IE' => $is_ie)
);

$template->set_filenames(array(
	'body' => 'css/' . $filename . '.css')
);
$template->assign_var_from_handle('EXT', 'body');

sql_close();

echo preg_replace('/\s\s+/', ' ', str_replace(array(nr(1), nr(), "\t"), '', preg_replace('!/\*.*?\*/!s', '', $template->vars['EXT'])));
exit;
