<?php

if (!defined('IN_APP')) exit;

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

// Protect against GLOBALS tricks
if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
    exit;
}

// Protect against _SESSION tricks
if (isset($_SESSION) && !is_array($_SESSION)) {
    exit;
}

// Be paranoid with passed vars
if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on') {
    $not_unset = array('_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_SESSION', '_ENV', '_FILES');

    // Not only will array_merge give a warning if a parameter
    // is not an array, it will actually fail. So we check if
    // _SESSION has been initialised.
    if (!isset($_SESSION) || !is_array($_SESSION)) {
        $_SESSION = array();
    }

    // Merge all into one extremely huge array; unset
    // this later
    $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_SESSION, $_ENV, $_FILES);

    foreach ($input as $varname => $void) {
        if (!in_array($varname, $not_unset)) {
            unset(${$varname});
        }
    }

    unset($input);
}

//
// Set the root path
if (!defined('ROOT')) {
    define('ROOT', './');
}

//
// Set the vendor path
if (!defined('VENDOR')) {
    define('VENDOR', ROOT . '../../vendor/');
}

//
// Start the main system
define('USE_CACHE', false);
define('STRIP', (get_magic_quotes_gpc()) ? true : false);

if (!defined('REQC')) {
    define('REQC', strpos(ini_get('request_order'), 'C') === false);
}

//
// Load basic libraries
//
require_once(ROOT.'interfase/constants.php');
require_once(VENDOR.'npi/cliws.php');
require_once(ROOT.'interfase/functions.php');
require_once(ROOT.'interfase/database.php');
require_once(ROOT.'interfase/fileupload.php');

//
// Class autoload
//
spl_autoload_register('app_autoload');
set_error_handler('msg_handler');

//
// Setup for working with Unicode data
//
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

//
// Initialize connection and objects
//
$db       = npi('htrd:mysql');

$user     = new user();
$cache    = new cache();
$template = new template();
$comments = new comments();
$upload   = new upload();

$config   = $cache->config();
