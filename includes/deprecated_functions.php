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


/*
*  Originally located in classes/permissions.class.php;
*  To be removed in v2.0 because those names caused double negatives in coding:
*    To see if something was readable for the user, you'd have to say !getDenyRead($module, $item_id0
*/
function getDenyRead($mod, $item_id = 0) {
	trigger_error("getDenyRead has been deprecated in v1.3 and will be removed in v2.0", E_USER_NOTICE );
    return !canView($mod, $item_id);
}
function getDenyEdit($mod, $item_id = 0) {
	trigger_error("getDenyEdit has been deprecated in v1.3 and will be removed in v2.0", E_USER_NOTICE );
    return !canEdit($mod, $item_id);
}
function getDenyAdd($mod, $item_id = 0) {
	trigger_error("getDenyAdd has been deprecated in v1.3 and will be removed in v2.0", E_USER_NOTICE );
    return !canAdd($mod, $item_id);
}
