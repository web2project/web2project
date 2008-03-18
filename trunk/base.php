<?php /* $Id$ $URL$ */

/* 
Copyright (c) 2007-2008 The web2Project Development Team <w2p-developers@web2project.net>
Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

This file is part of web2Project.

web2Project is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

web2Project is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2Project; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

ini_set('display_errors', 1);
error_reporting(E_ALL & ~ E_NOTICE);
define('W2P_PERFORMANCE_DEBUG', false);

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

global $baseDir;
global $baseUrl;

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

// required includes for start-up
global $w2Pconfig;
$w2Pconfig = array();

?>