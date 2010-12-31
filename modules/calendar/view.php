<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$event_id = (int) w2PgetParam($_GET, 'event_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();
$canRead = $perms->checkModuleItem($m, 'view', $event_id);
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}
$canEdit = $perms->checkModuleItem($m, 'edit', $event_id);

// check if this record has dependencies to prevent deletion
$msg = '';
$event = new CEvent();
$canDelete = $event->canDelete($msg, $event_id);

// load the record data
if (!$event->load($event_id)) {
	$AppUI->setMsg('Event');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

//check if the user has view permission over the project
if ($event->event_project && !$perms->checkModuleItem('projects', 'view', $event->event_project)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// load the event types
$types = w2PgetSysVal('EventType');

// load the event recurs types
$recurs = array('Never', 'Hourly', 'Daily', 'Weekly', 'Bi-Weekly', 'Every Month', 'Quarterly', 'Every 6 months', 'Every Year');

$assigned = $event->getAssigned();

if (($event->event_owner != $AppUI->user_id) && !canView('admin')) {
	$canEdit = false;
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$start_date = $event->event_start_date ? new CDate($event->event_start_date) : new CDate();
$end_date = $event->event_end_date ? new CDate($event->event_end_date) : new CDate();
if ($event->event_project) {
	$project = new CProject();
	$event_project = $project->load($event->event_project)->project_name;
}

// setup the title block
$titleBlock = new CTitleBlock('View Event', 'myevo-appointments.png', $m, $m . '.' . $a);
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell('
		<form action="?m=calendar&amp;a=addedit" method="post" accept-charset="utf-8">
			<input type="submit" class="button" value="' . $AppUI->_('new event') . '" />
		</form>', '', '', '');
}
$titleBlock->addCrumb('?m=calendar&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE), 'month view');
$titleBlock->addCrumb('?m=calendar&amp;a=day_view&amp;date=' . $start_date->format(FMT_TIMESTAMP_DATE) . '&amp;tab=0', 'day view');
if ($canEdit) {
	$titleBlock->addCrumb('?m=calendar&amp;a=addedit&amp;event_id=' . $event_id, 'edit this event');
	if ($canDelete) {
		$titleBlock->addCrumbDelete('delete event', $canDelete, $msg);
	}
}
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt() {
	if (confirm( "<?php echo $AppUI->_('eventDelete', UI_OUTPUT_JS); ?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<form name="frmDelete" action="./index.php?m=calendar" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
</form>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
	<tr>
		<td valign="top" width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" width="100%">
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Event Title'); ?>:</td>
				<td class="hilite" width="100%"><?php echo $event->event_title; ?></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
				<td class="hilite" width="100%"><?php echo $AppUI->_($types[$event->event_type]); ?></td>
			</tr>	
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
				<td class="hilite" width="100%"><a href='?m=projects&a=view&project_id=<?php echo $event->event_project ?>'><?php echo $event_project; ?></a></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Starts'); ?>:</td>
				<td class="hilite"><?php echo $AppUI->formatTZAwareTime($event->event_start_date, $df . ' ' . $tf); ?></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Ends'); ?>:</td>
				<td class="hilite"><?php echo $AppUI->formatTZAwareTime($event->event_end_date, $df . ' ' . $tf); ?></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Recurs'); ?>:</td>
				<td class="hilite"><?php echo $AppUI->_($recurs[$event->event_recurs]) . ($event->event_recurs ? ' (' . $event->event_times_recuring . '&nbsp;' . $AppUI->_('times') . ')' : ''); ?></td>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Attendees'); ?>:</td>
				<td class="hilite">
					<?php
						if (is_array($assigned)) {
							$start = false;
							foreach ($assigned as $user) {
								if ($start)
									echo '<br/>';
								else
									$start = true;
								echo $user;
							}
						}
					?>
			</tr>
			</table>
		</td>
		<td width="50%" valign="top">
			<strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="0" cellpadding="2" border="0" width="100%">
				<tr>
					<td class="hilite">
						<?php echo w2p_textarea($event->event_description); ?>&nbsp;
					</td>
				</tr>
			</table>
			<?php
				$custom_fields = new w2p_Core_CustomFields($m, $a, $event->event_id, 'view');
				$custom_fields->printHTML();
			?>
		</td>
	</tr>
</table>