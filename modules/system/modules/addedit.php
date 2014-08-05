<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$mod_id = (int) w2PgetParam($_GET, 'mod_id');
$view   = w2PgetParam($_GET, 'v');

$module = new w2p_System_Module();
$module->load($mod_id);

$obj = $module;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
$canRead = canView('system');

if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

if (!$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

//TODO: generate per-module filter list
$filter = array($module->permissions_item_field, 'user_password', 'user_parent',
        'task_updator', 'task_order', 'task_client_publish', 'task_dynamic',
        'task_notify', 'task_departments', 'task_contacts', 'task_custom',
        'task_allow_other_user_tasklogs', 'tracked_dynamics', 'tracking_dynamics',
        'task_target_budget', 'task_project', 'task_parent', 'task_milestone',
        'task_access', 'project_contacts');
//$filter = array('project_id', 'project_status', 'project_active',
//	'project_parent', 'project_color_identifier',
//	'project_original_parent', 'project_departments', 'project_contacts',
//	'project_private', 'project_type', 'project_last_task', 'project_scheduled_hours');

if ('CProjectDesigner' == $module->mod_main_class) {
    $module->mod_main_class = 'CTask';
}
if ('CProject' == $module->mod_main_class && 'tasklist' == $view) {
    $module->mod_main_class = 'CTask';
}
$properties = get_class_vars($module->mod_main_class);

//TODO: Figure out a way to auto-load subclasses. Applicable for: Tasks, Files, Forum
if ('CTask' == $module->mod_main_class) {
    $properties = array_merge($properties, get_class_vars('CTask_Log'));
}

foreach ($filter as $field => $value) {
    unset($properties[$value]);
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Customize '.$module->mod_name.' Module :: '.
        $view, 'modules/system/control-center.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'modules list');
$titleBlock->show();

$fields = w2p_System_Module::getSettings($module->mod_directory, $view);
$fields = array_diff($fields, $filter);
foreach ($fields as $field => $text) {
    $fieldList[] = $field;
    $fieldNames[] = $text;
}
$orderMax = count($properties) + count($fields);
?>

<form name="frmConfig" id="frmConfig" action="?m=<?php echo $m; ?>&u=modules" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_module_config_aed" />
	<input type="hidden" name="mod_id" value="<?php echo $mod_id; ?>" />
	<input type="hidden" name="module_config_name" value="<?php echo $view ?>" />

	<table id="tblConfig" class="std tbl list well">
		<tr>
			<th colspan="2"><?php echo $AppUI->_('Order'); ?></th>
			<th><?php echo $AppUI->_('Object Property'); ?></th>
			<th><?php echo $AppUI->_('Display Name'); ?></th>
		</tr>
		<?php
		$order = 1;
		foreach ($fields as $field => $text) {
			?><tr>
				<td>
					<input type="checkbox" name="display[<?php echo $field; ?>]" checked="checked" />
				</td>
				<td>
					<?php echo arraySelect(range(0, $orderMax), "order[$field]", 'class="text" size="1"', $order); ?>
				</td>
				<td class="center">
					<input type="hidden" name="displayFields[]" value="<?php echo $field; ?>" />
					<?php echo $field; ?>
				</td>
				<td class="center">
					<input type="text" name="displayNames[]" value="<?php echo htmlspecialchars($text, ENT_QUOTES); ?>" size="25" maxlength="50" class="text" />
				</td>
			</tr><?php
			$order++;
			unset($properties[$field]);
		}
		foreach ($properties as $property => $value) {
			$fieldname_pieces = explode('_', $property);
            unset($fieldname_pieces[0]);
            $value = ucwords(implode(' ', $fieldname_pieces));
            ?><tr>
				<td>
					<input type="checkbox" name="display[<?php echo $property; ?>]" />
				</td>
				<td>
					<input type="hidden" name="order[<?php echo $property; ?>]" value="<?php echo $order ?>" />
				</td>
				<td class="center">
					<input type="hidden" name="displayFields[]" value="<?php echo $property; ?>" />
					<?php echo $property; ?>
				</td>
				<td class="center">
					<input type="text" name="displayNames[]" value="<?php echo $AppUI->_($value); ?>" size="25" maxlength="50" class="text" />
				</td>
			</tr><?php
			$order++;
		}
		?>
  		<tr>
          	<td colspan="2">
          		<input class="button btn btn-danger" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" />
            </td>
          	<td colspan="2" class="center">
          		<input class="button btn btn-primary" type="submit" name="submit" value="<?php echo $AppUI->_('save'); ?>" />
          	</td>
    	</tr>
	</table>
</form>
