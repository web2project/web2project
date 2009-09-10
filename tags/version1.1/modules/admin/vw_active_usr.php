<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

require_once ($AppUI->getModuleClass('companies'));
global $w2Pconfig, $canEdit, $stub, $where, $orderby;

$users = w2PgetUsersList($stub, $where, $orderby);
$canLogin = true;

require W2P_BASE_DIR . '/modules/admin/vw_usr.php';