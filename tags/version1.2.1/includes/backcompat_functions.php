<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which may not be
*   available in all versions of PHP.  Therefore, we have to wrap the
*   functionality in function_exists stuff and all that.  In the documentation
*   for each function, you must describe:
*
*    * the specific version of PHP or extension the regular function requires.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to change minimum version for PHP compatibility.
*/

if (!defined('PHP_VERSION')) {
  define('PHP_VERSION', phpversion());
}