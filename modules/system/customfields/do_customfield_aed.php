<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new w2p_Controllers_Base(
                    new w2p_Core_CustomFieldManager(), $delete, 'Custom Fields', 'm=system&u=customfields', 'm=system'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);