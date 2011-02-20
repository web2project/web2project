<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$filter = array('project_id', 'project_status', 'project_active', 
	'project_parent', 'project_percent_complete', 'project_company',
	'project_original_parent', 'project_departments', 'project_contacts',
	'project_private', 'project_type');

$project = new CProject();
$properties = get_class_vars(get_class($project));
foreach ($filter as $field => $value) {
	unset($properties[$value]);
}

// setup the title block
$titleBlock = new CTitleBlock('Configure Projects Module', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'modules list');
$titleBlock->show();

$fields = w2p_Core_Module::getSettings($m, 'index_list');
$fields = array_diff($fields, $filter);
foreach ($fields as $field => $text) {
    $fieldList[] = $field;
    $fieldNames[] = $text;
}

?>
<form name="frmConfig" id="frmConfig" action="./index.php?m=system" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_module_config" />
	<input type="hidden" name="module_name" value="<?php echo $m; ?>" />
	<input type="hidden" name="module_config_name" value="index_list" />

	<table id="tblConfig" border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
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
					<input type="hidden" name="order[<?php echo $field; ?>]" value="<?php echo $order ?>" />
					(add buttons)
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
					<input type="text" name="displayNames[]" value="<?php echo $AppUI->_($property); ?>" size="25" maxlength="50" class="text" />
				</td>
			</tr><?php
			$order++;
		}
		?>
  		<tr>
          	<td colspan="2">
          		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" />
          	<td colspan="2" class="center">
          		<input class="button" type="submit" name="submit" value="<?php echo $AppUI->_('save'); ?>" />
          	</td>
    	</tr>
	</table>
</form>