<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
define('IN_APP', true);

if (!defined('ROOT')) {
	define('ROOT', './');
}

require_once(ROOT . 'interfase/common.php');

$user->init(false);
$user->setup();

$allowed = w('css js');
$browsers = array('firefox' => 'Gecko', 'ie' => 'IE');

//
// Receive data
//
$filename = request_var('filename', '');
$format = request_var('format', '');

if (empty($filename) || !preg_match('#[a-z\_]+#i', $filename) || !in_array($format, $allowed)) {
	fatal_error();
}

$filepath = ROOT . 'template/' . $format . '/' . $filename . '.' . $format;
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

$is = w();
foreach ($browsers as $browser_k => $browser_v) {
	$is[$browser_k] = (strstr($user->browser, $browser_v)) ? true : false;
}

if (strstr($user->browser, 'compatible') || $is['firefox']) {
	ob_start('ob_gzhandler');
}

v_style(array(
	'FF' => $is['firefox'],
	'IE' => $is['ie'])
);

$template->set_filenames(array(
	'body' => $format . '/' . $filename . '.' . $format)
);
$template->assign_var_from_handle('EXT', 'body');
$code = $template->vars['EXT'];

sql_close();

switch ($format) {
	case 'css':
		$code = preg_replace('/\s\s+/', ' ', str_replace(array(nr(1), nr(), "\t"), '', preg_replace('!/\*.*?\*/!s', '', $code)));

		$content_type = 'text/css; charset=utf-8';
		break;
	case 'js':
		require_once(ROOT . 'interfase/jsmin.php');
		// $code = JSMin::minify($code);

		$content_type = 'application/javascript; charset=utf-8';
		break;
}

//
// Send headers to client
//
header('Content-type: ' . $content_type);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 30)) . ' GMT');
status('200 OK');

echo $code;
exit;