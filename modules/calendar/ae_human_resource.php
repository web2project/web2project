	<?php

	if (!defined('DP_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	global $AppUI, $users, $event_id, $obj, $currentTabId, $is_clash;

// Load the assignees
	$assigned = array();
	if ($is_clash) {
		$assignee_list = $_SESSION['add_event_attendees'];
		if (isset($assignee_list) && $assignee_list) {
			$event = new CEvent();
			$assigned = $event->getAssigneeList($assignee_list);
		}
		// Now that we have loaded the possible replacement event,  remove the stored
		// details, NOTE: This could cause using a back button to make things break,
		// but that is the least of our problems.
		unset($_SESSION['add_event_post']);
		unset($_SESSION['add_event_attendees']);
		unset($_SESSION['add_event_mail']);
		unset($_SESSION['add_event_clash']);
		unset($_SESSION['event_is_clash']);
	} else {
		if ($event_id == 0) {
			$assigned[$AppUI->user_id] = $AppUI->user_display_name;
		} else {
			$assigned = $obj->getAssigned();
		}
	}
	?>
	<script language="javascript">
		<?php
//		echo "var projTasksWithEndDates=new Array();\n";
//		$keys = array_keys($projTasksWithEndDates);
//		for ($i = 1, $xi = sizeof($keys); $i < $xi; $i++) {
//			array[task_is] = end_date, end_hour, end_minutes
//			echo ('projTasksWithEndDates[' . $keys[$i] . ']=new Array("'
//				. $projTasksWithEndDates[$keys[$i]][1] . '", "'
//				. $projTasksWithEndDates[$keys[$i]][2] . '", "'
//				. $projTasksWithEndDates[$keys[$i]][3] ."\");\n");
//		}
//		?>
	</script>
<form action="?m=calendar&a=addedit&event_id=<?php echo $event_id; ?>"
	  method="post" name="resourceFrm">
	<input type="hidden" name="sub_form" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input name="event_assigned" type="hidden" value="<?php echo
	$initPercAsignment;?>"/>
	<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std">
		<tr>
			<td valign="top" align="center">
				<table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><?php echo $AppUI->_('Human Resources');?>:</td>
						<td><?php echo $AppUI->_('Invited to Event');?>:</td>
					</tr>
					<tr>
						<td>
							<?php echo arraySelect($users, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
						</td>
						<td>
							<?php echo arraySelect($assigned, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<table>
								<tr>
									<td align="right"><input type="button" class="button" value="&gt;" onclick="javascript:addUser(document.resourceFrm)" /></td>
									<td align="left"><input type="button" class="button" value="&lt;" onclick="javascript:removeUser(document.resourceFrm)" /></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
				<td><label for="mail_invited"><?php echo $AppUI->_('Mail Attendees?'); ?></label> <input type="checkbox" name="mail_invited" id="mail_invited" checked="checked" /></td>
		</tr>
	</table>
	<input type="hidden" name="hassign" />
</form>
<script language="javascript">
	subForm.push(new FormDefinition(<?php echo $currentTabId; ?>, document.resourceFrm, checkResource, saveResource));
</script>
