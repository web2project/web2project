<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (!canView('system')) { // let's see if the user has sys access
	$AppUI->redirect(ACCESS_DENIED);
}
phpinfo();