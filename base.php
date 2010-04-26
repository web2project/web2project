<?php /* $Id$ $URL$ */

ini_set('display_errors', 0);
if(defined('E_DEPRECATED')){
	// since php 5.3
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
	error_reporting(E_ALL & ~ E_NOTICE);
}
//error_reporting(-1);
define('W2P_PERFORMANCE_DEBUG', false);
define('MIN_PHP_VERSION', '5.2.0');

//Performance Debug Initialization
if (W2P_PERFORMANCE_DEBUG) {
	global $w2p_performance_time, $w2p_performance_dbtime, $w2p_performance_old_dbqueries, $w2p_performance_dbqueries, $w2p_performance_acltime, $w2p_performance_aclchecks, $w2p_performance_memory_marker, $w2p_performance_setuptime;
	$w2p_performance_time = array_sum(explode(' ', microtime()));
	if (function_exists('memory_get_usage')) {
		$w2p_performance_memory_marker = memory_get_usage();
	}
	$w2p_performance_acltime = 0;
	$w2p_performance_aclchecks = 0;
	$w2p_performance_dbtime = 0;
	$w2p_performance_old_dbqueries = 0;
	$w2p_performance_dbqueries = 0;
}

$baseDir = dirname(__file__);

// only rely on env variables if not using a apache handler
function safe_get_env($name) {
	if (isset($_SERVER[$name])) {
		return $_SERVER[$name];
	} elseif (strpos(php_sapi_name(), 'apache') === false) {
		getenv($name);
	} else {
		return '';
	}
}

// automatically define the base url
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= safe_get_env('HTTP_HOST');
$pathInfo = safe_get_env('PATH_INFO');
if ($pathInfo) {
	$baseUrl .= str_replace('\\', '/', dirname($pathInfo));
} else {
	$baseUrl .= str_replace('\\', '/', dirname(safe_get_env('SCRIPT_NAME')));
}

$baseUrl = preg_replace('#/$#D', '', $baseUrl);
// Defines to deprecate the global baseUrl/baseDir
define('W2P_BASE_DIR', $baseDir);
define('W2P_BASE_URL', $baseUrl);

// Include the PHPGACL library
require_once W2P_BASE_DIR . '/lib/phpgacl/gacl.class.php';
require_once W2P_BASE_DIR . '/lib/phpgacl/gacl_api.class.php';

// Set the ADODB directory
if (!defined('ADODB_DIR')) {
	define('ADODB_DIR', W2P_BASE_DIR . '/lib/adodb');
}

/*
 *  This  is set to get past the dotProject security sentinel.  It is only
 * required during the conversion process to load config.php.  Hopefully we
 * will be able to kill this off down the road or someone can come up with a
 * better idea.
 */
define('DP_BASE_DIR', $baseDir);

// required includes for start-up
global $w2Pconfig;
$w2Pconfig = array();

// Start up mb_string UTF-8 if available
if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding('UTF-8');
}