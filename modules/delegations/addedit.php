<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->loadCalendarJS();

$deleg_id = (int) w2PgetParam($_GET, 'delegation_id', 0);
$project_id = (int) w2PgetParam($_GET, 'project_id', 0);
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$deleg = new CDelegation();
$deleg->delegation_id = $deleg_id;

$obj = $deleg;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $deleg = $obj;
    $deleg_id = $deleg->delegation_id;
} else {
    $deleg->load($deleg_id);
}
if (!$deleg && $deleg_id > 0) {
    $AppUI->setMsg('Delegation');
    $AppUI->setMsg('invalid ID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

if ($deleg_id) {
    $project_id = $deleg->delegation_project;
    $task_id = $deleg->delegation_task;
    $deleg_date = $deleg->delegation_start_date;
} else {
    $deleg->delegation_project = $project_id;
    $deleg->delegation_task    = $task_id;
}
$deleg_date = (int)$deleg->delegation_start_date ?
	      new w2p_Utilities_Date($AppUI->formatTZAwareTime($deleg->delegation_start_date, '%Y-%m-%d %T')) : null;

// get the task and project name for displaying
$project = new CProject();
$project->load($project_id);

$task = new CTask();
$task->load($task_id);

$start = (int) w2PgetConfig('cal_day_start', 8);
$end = (int) w2PgetConfig('cal_day_end', 17);
$inc = (int) w2PgetConfig('cal_day_increment', 15);

$hours = array();
for ($current = $start; $current < $end + 1; $current++) {
    $current_key = ($current < 10) ? '0' . $current : $current;

    if ($ampm) {
		//User time format in 12hr
		$hours[$current_key] = ($current > 12 ? $current - 12 : $current);
	} else {
		//User time format in 24hr
		$hours[$current_key] = $current;
	}
}

$minutes = array();
$minutes['00'] = '00';
for ($current = 0 + $inc; $current < 60; $current += $inc) {
	$minutes[$current] = $current;
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

// setup the title block
$ttl = $deleg_id ? 'Edit Delegation' : 'Add Delegation';
$titleBlock = new w2p_Theme_TitleBlock($AppUI->_($ttl), 'delegation.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, 'delegations list');
$canDelete = $deleg->canDelete();
if ($canDelete && $deleg_id) {
    if (!isset($msg)) {
        $msg = '';
    }
    $titleBlock->addCrumbDelete('delete delegation', $canDelete, $msg);
}
$titleBlock->show();

?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var f = document.updateFrm;
	f.submit();
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Delegation', UI_OUTPUT_JS); ?>?" )) {
		var f = document.updateFrm;
		f.del.value='1';
		f.submit();
	}
}
</script>

<form name="updateFrm" action="?m=delegations" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_delegation_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="delegation_id" value="<?php echo $deleg_id; ?>" />
    <input type="hidden" name="delegating_user_id" value="<?php echo $deleg->delegating_user_id; ?>" />
    <input type="hidden" name="delegated_to_user_id" value="<?php echo $deleg->delegated_to_user_id; ?>" />
    <input type="hidden" name="delegation_task" value="<?php echo $deleg->delegation_task; ?>" />
    <input type="hidden" name="delegation_project" value="<?php echo $deleg->delegation_project; ?>" />
    <input type="hidden" name="datePicker" value="delegation" />

    <table width="100%" border="0" cellpadding="3" cellspacing="3" class="std addedit">
	<tr>
	    <td></td>
	    <td width="100%" align="center">
		<table border="0" cellpadding="3" cellspacing="3">
		    <tr>
		        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
		        <td align="left"><b><?php echo $project->project_name; ?></b></td>
			<td colspan="5"></td>
		    </tr>
		    <tr>
		        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
		        <td align="left"><b><?php echo $task->task_name; ?></b></td>
			<td colspan="5"></td>
		    </tr>
		    <tr>
		        <td align="right" nowrap="nowrap"><div id="do_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		        <td nowrap="nowrap" width="1%">
			    <input type='hidden' id='delegation_start_date' name='delegation_start_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
			    <input type='text' onchange="setDate_new('updateFrm', 'start_date');" class='text' style='width:120px;' id='start_date' name='start_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
			    <a onclick="return showCalendar('start_date', '<?php echo $df ?>', 'updateFrm', null, true, true)" href="javascript: void(0);">
			    <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
			    </a>
		        </td>
		        <?php
		            echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'start_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
			    echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
			    echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'start_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
			    if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
			        echo '<td nowrap="nowrap" width="1%"><input type="text" name="start_hour_ampm" id="start_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
			    } else {
				echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
			    }
		        ?>
			<td></td>
		    </tr>
		    <tr>
	                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:&nbsp;</div></td>
	                <td colspan="6"><input type="text" name="delegation_name" class="text" size="60" value="<?php echo $deleg->delegation_name ?>" /></td>
                        <td align="right"></td>
		    </tr>
		    <tr>
		        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:&nbsp;</div></td>
		        <td colspan="6"><textarea name="delegation_description" class="textarea" cols="60" rows="5"><?php echo $deleg->delegation_description ?></textarea></td>
			<td></td>
		</table>
	    </td>
	    <td></td>
	</tr>
	<tr>
	    <td>
	        <input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to cancel?', UI_OUTPUT_JS); ?>')){location.href = './index.php?m=delegations';}" />
	    </td>
	    <td width="100%">&nbsp;</td>
	    <td align="right">
	        <input type="button" class="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
	    </td>
	</tr>
    </table>
</form>
