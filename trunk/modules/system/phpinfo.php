<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = &$AppUI->acl();
if (!$perms->checkModule('system', 'view')) { // let's see if the user has sys access
	$AppUI->redirect('m=public&a=access_denied');
}
phpinfo(); 
?>