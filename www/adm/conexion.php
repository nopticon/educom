<?php

require_once(__DIR__ . '/../../vendor/npi/cliws.php');

// $db = npi('htrd:mysql');

session_start();

require_once('library/functions.php');

// $platform_path = __DIR__ . '/../orion/';
define('ROOT', __DIR__ . '/../orion/');
define('IN_APP', true);

//
// Start the main system
define('USE_CACHE', false);
define('STRIP', (get_magic_quotes_gpc()) ? true : false);

if (!defined('REQC')) {
	define('REQC', strpos(ini_get('request_order'), 'C') === false);
}

require_once(ROOT.'interfase/constants.php');
require_once(ROOT.'interfase/functions.php');
require_once(ROOT.'interfase/database.php');
// require_once(ROOT.'interfase/fileupload.php');

//
// Class autoload
//
spl_autoload_register('app_autoload');
set_error_handler('msg_handler');

//setup php for working with Unicode data
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

$db = npi('htrd:mysql');

$user = new user();
$cache = new cache();
$template = new template();
$comments = new comments();
$upload = new upload();

$config = $cache->config();

$user->init();
$user->setup();

if (!$user->is('member')) {
	do_login();
}
