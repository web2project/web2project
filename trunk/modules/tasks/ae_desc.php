<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// $Id$
global $AppUI, $task_id, $obj, $users, $task_access, $department_selection_list;
global $task_parent_options, $w2Pconfig, $projects, $task_project, $can_edit_time_information, $tab;

$perms = &$AppUI->acl();
?>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" name="detailFrm" accept-charset="utf-8">
<input type="hidden" name="dosql" value="do_task_aed" />
<input type="hidden" name="sub_form" value="1" />
<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
<table class="std" width="100%" border="1" cellpadding="4" cellspacing="0">
<tr>
	<td width="50%" valign='top'>
	    <table border="0">
	    	<tr>
	    		<td>
				    <?php
              if ($can_edit_time_information) {
              ?>
                <?php echo $AppUI->_('Task Owner'); ?><br />
  							<?php echo arraySelect($users, 'task_owner', 'class="text"', !isset($obj->task_owner) ? $AppUI->user_id : $obj->task_owner); ?>
  								<br />
  						<?php
              }
            ?>
						<?php echo $AppUI->_('Access'); ?>
						<br />
						<?php echo arraySelect($task_access, 'task_access', 'class="text"', intval($obj->task_access), true); ?>
						<br /><?php echo $AppUI->_('Web Address'); ?>
						<br /><input type="text" class="text" name="task_related_url" value="<?php echo $obj->task_related_url; ?>" size="40" maxlength="255" />
					</td>
					<td valign='top'>
						<?php echo $AppUI->_('Task Type'); ?>
						<br />
						<?php
              $task_types = w2PgetSysVal('TaskType');
              echo arraySelect($task_types, 'task_type', 'class="text"', $obj->task_type, false);
            ?>
						<br /><br />
  					<?php
              if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
              	echo '<input type="button" class="button" value="' . $AppUI->_('Select contacts...') . '" onclick="javascript:popContacts();" />';
              }
              // Let's check if the actual company has departments registered
              if (count($department_selection_list) > 1) {
                ?>
  								<br />
  								<?php echo $AppUI->_('Department'); ?><br />
                  <?php echo arraySelect($department_selection_list, 'dept_ids[]', 'class="text" size="1"', $obj->task_departments); ?>
  							<?php
              }
            ?>
				  </td>
        </tr>
    		<tr>
    			<td><?php echo $AppUI->_('Task Parent'); ?>:</td>
    			<td><?php echo $AppUI->_('Target Budget'); ?>:</td>
    		</tr>
		<tr>
			<td>
				<select name='task_parent' class='text'>
					<option value='<?php echo $obj->task_id; ?>'><?php echo $AppUI->_('None'); ?></option>
					<?php echo $task_parent_options; ?>
				</select>
			</td>
			<td><?php echo $w2Pconfig['currency_symbol'] ?><input type="text" class="text" name="task_target_budget" value="<?php echo $obj->task_target_budget; ?>" size="10" maxlength="10" /></td>
		</tr>
	<?php if ($task_id > 0) { ?>
		<tr>
			<td>
				<?php echo $AppUI->_('Move this task (and its children), to project'); ?>:
			</td>
		</tr>
		<tr>
			<td>
				<?php echo arraySelect($projects, 'new_task_project', 'size="1" class="text" id="medium" onchange="submitIt(document.editFrm)"', $task_project); ?>
			</td>
		</tr>
	<?php } ?>
		</table>
	</td>
	<td valign="top" align="center">
		<table><tr><td align="left">
		<?php echo $AppUI->_('Description'); ?>:
		<br />
		<textarea name="task_description" class="textarea" cols="60" rows="10"><?php echo $obj->task_description; ?></textarea>
		</td></tr></table><br />
		<?php
global $m;
$custom_fields = new CustomFields($m, 'addedit', $obj->task_id, 'edit');
$custom_fields->printHTML();
?>
	</td>
</tr>
</table>
</form>
<script language="javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.detailFrm, checkDetail, saveDetail));
</script>