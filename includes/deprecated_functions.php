<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which will be
*   deprecated in coming releases.  In the documentation for each function,
*   you must describe two things:
*
*    * the specific version of web2project where the behavior will change; and
*    * a reference to the new/proper way of performing the same functionality.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to delete functions.
*
* WARNING: This file does not identify class-level method deprecations.
*   In order to find those, you'll have to explore the individual classes.
*/

/**
 * This function is now deprecated and will be removed.
 * In the interim it now does nothing.
 * TODO:  Remove for v3.0 - dkc 27 Nov 2010
 */
function dpRealPath($file) {
	trigger_error("The dpRealPath function has been deprecated and will be removed in v3.0.", E_USER_NOTICE );
    return $file;
}
