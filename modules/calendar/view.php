<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$event_id = (int) w2PgetParam($_GET, 'event_id', 0);



$event = new CEvent();
$event->event_id = $event_id;

$canEdit   = $event->canEdit();
$canRead   = $event->canView();
$canAdd    = $event->canCreate();
$canAccess = $event->canAccess();
$canDelete = $event->canDelete();

if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$event->loadFull($event_id);
if (!$event) {
	$AppUI->setMsg('Event');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}



// check permissions for this record
$perms = &$AppUI->acl();


//check if the user has view permission over the project
if ($event->event_project && !$perms->checkModuleItem('projects', 'view', $event->event_project)) {
	$AppUI->redirect(ACCESS_DENIED);
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

$start_date = $event->event_start_date ? new w2p_Utilities_Date($event->event_start_date) : new w2p_Utilities_Date();
$end_date = $event->event_end_date ? new w2p_Utilities_Date($event->event_end_date) : new w2p_Utilities_Date();
if ($event->event_project) {
	$project = new CProject();
	$event_project = $project->load($event->event_project)->project_name;
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Event', 'myevo-appointments.png', $m, $m . '.' . $a);
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
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->df .= ' ' . $tf;
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
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
	<tr>
		<td valign="top" width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" width="100%">
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Event Title'); ?>:</td>
                <?php echo $htmlHelper->createCell('event_name', $event->event_name); ?>
			</tr>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                <?php echo $htmlHelper->createCell('event_type', $AppUI->_($types[$event->event_type])); ?>
			</tr>
			<?php if($event->event_project) { ?>
			<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                <td class="hilite" style="background-color:#<?php echo $event->project_color_identifier; ?>">
					<font color="<?php echo bestColor($event->project_color_identifier); ?>">
						<a href='?m=projects&a=view&project_id=<?php echo $event->event_project ?>'><?php echo $event_project; ?></a>
					</font>
                </td>
			</tr>
			<?php } ?>
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
                    <?php echo $htmlHelper->createCell('event_description', $event->event_description); ?>
				</tr>
			</table>
			<?php
				$custom_fields = new w2p_Core_CustomFields($m, $a, $event->event_id, 'view');
				$custom_fields->printHTML();
			?>
		</td>
	</tr>
</table>