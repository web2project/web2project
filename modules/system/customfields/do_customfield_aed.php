<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
 * TODO: This is more than a little hacky.. trying to come up with a better
 *   approach but haven't found one yet.
 */
$_POST['field_datatype'] = 'alpha';
$_POST['field_page'] = 'addedit';
$module_id = (int) w2PgetParam($_POST, 'module', 0);
$module = new w2p_Core_Module();
$module->load($module_id);
$_POST['field_module'] = $module->mod_name;

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new w2p_Controllers_Base(
                    new w2p_Core_CustomFieldManager(), $delete, 'Custom Fields', 'm=system&u=customfields', 'm=system&u=customfields&a=addedit'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);