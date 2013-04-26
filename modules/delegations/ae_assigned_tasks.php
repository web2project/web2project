<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $showEditCheckbox, $f, $f2, $user_id, $showIncomplete, $search_text;
global $user_list, $priorities, $durnTypes;

$AppUI->loadCalendarJS();

// remove the current user from the selection list
unset($user_list[$user_id]);

$project = new CProject;
$allowedProjects = $project->getAllowedSQL($AppUI->user_id,'pr.project_id');

$task = new CTask;
$allowedTasks = $task->getAllowedSQL($AppUI->user_id, 'ta.task_id');

$q = new w2p_Database_Query;
$q->addQuery('distinct(ta.task_id), ta.*');
$q->addQuery('project_name, pr.project_id, project_color_identifier');
$q->addQuery('tp.task_pinned');
$q->addQuery('ut.user_task_priority');
$q->addQuery('DATEDIFF(ta.task_end_date, NOW()) as task_due_in');
$q->addQuery('tlog.task_log_problem');

$q->addTable('projects', 'pr');
$q->addTable('tasks', 'ta');
$q->addTable('user_tasks', 'ut');
$q->leftJoin('user_task_pin', 'tp', 'tp.task_id = ta.task_id and tp.user_id = ' . (int)$user_id);
$q->leftJoin('project_departments', 'project_departments', 'pr.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = ta.task_id AND tlog.task_log_problem > 0');

if ((int)$f2) {
	$q->addWhere('pr.project_company = "' . (int)$f2 . '"');
}

$q->addWhere('ut.task_id = ta.task_id');

$q->addWhere('ta.task_status = 0');
$q->addWhere('pr.project_id = ta.task_project');

$q->addWhere('project_active = 1');
$q->addWhere('task_dynamic <> 1');

if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}

if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}

if ($showIncomplete) {
	$q->addWhere('(task_percent_complete < 100 OR task_percent_complete IS NULL)');
}

$f = (($f) ? $f : '');
switch ($f) {
	case 'myfinished7days':
		$q->addWhere('task_percent_complete = 100');
		$fdate = new w2p_Utilities_Date();
		$fdate->addDays(-7);
		$q->addWhere('task_end_date >= \'' . $fdate->format(FMT_DATETIME_MYSQL) . '\'');
		$q->addWhere('ut.user_id = ' . (int)$user_id);
		break;
	case 'myunfinished':
		$q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
		$q->addWhere('ut.user_id = ' . (int)$user_id);
		break;
	case 'taskcreated':
		$q->addWhere('task_creator = ' . (int)$user_id);
		break;
	case 'taskowned':
		$q->addWhere('task_owner = ' . (int)$user_id);
		break;
	default:
		$q->addWhere('ut.user_id = ' . (int)$user_id);
		break;
}

if ($search_text != '') {
	$q->addWhere('( task_name LIKE (\'%' . $search_text . '%\') OR task_description LIKE (\'%' . $search_text . '%\') )');
}

$q->addOrder('task_end_date, task_start_date, task_priority');
$tasks = $q->loadList();

foreach ($tasks as &$row) {
	// add information about delegations into the page output
	$q->clear();
	$q->addQuery('ud.delegated_to_user_id, ud.delegating_user_id');
	$q->addQuery('ud.delegation_percent_complete, c2.contact_display_name AS delegating');
	$q->addQuery('c.contact_display_name AS delegated, ud.delegation_rejection_date');
	$q->addTable('user_delegations', 'ud');
	$q->addJoin('users', 'u', 'u.user_id = ud.delegated_to_user_id', 'inner');
	$q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id', 'inner');
	$q->addJoin('users', 'u2', 'u2.user_id = ud.delegating_user_id', 'inner');
	$q->addJoin('contacts', 'c2', 'u2.user_contact = c2.contact_id', 'inner');
	$q->addWhere('ud.delegation_task = ' . (int)$row['task_id']);
	$q->addOrder('delegation_start_date');
	$row['delegations'] = $q->loadList();
} 

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('delegations', 'assigned_tasks');
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

$priorities = array('1' => 'high', '0' => 'normal', '-1' => 'low');
$durnTypes = w2PgetSysVal('TaskDurationType');

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

$deleg_date = new w2p_Utilities_Date();

$columns = array_merge(array('__edit', '__pin', '__log'), $fieldList, array('task_selection'));

?>

<script language="javascript" type="text/javascript">
function clickedTask(id) {
	var div = document.getElementById('delegate_block');
	var boxes = document.getElementsByName('selected_task[]');
	for (var i=0, l=boxes.length; i < l; i++) {
		if (boxes[i].checked) {
			div.style.display = '';
			return;
		}
	}
	div.style.display = 'none';
}

function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}
</script>

<form name="delegateFrm" action="?m=delegations" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_delegate_aed" />
    <input type="hidden" name="datePicker" value="deleg" />
    <table class="tbl" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td colspan="20">
	    <table id="tblDelegAssignTasks" class="tbl list">
		<tr>
		    <th></th><th></th><th></th>
		    <?php 
		    foreach ($fieldNames as $index => $name) {
		        echo '<th nowrap="nowrap">' . $AppUI->_($fieldNames[$index]) . '</th>';
		    }
		    ?>
		    <th><?php echo $AppUI->_('Selection'); ?></th>
		</tr>

		<?php
		$showEditCheckbox = true;
		foreach ($tasks as $task) {
		    echo showtask($task, 0, $columns, true);
		}
		?>
		<tr id="delegate_block" style="display: none">
		    <td colspan="7">&nbsp;</td>
		    <td colspan="8"><div style=" padding: 10px;">
			<table cellspacing="0" cellpadding="0" border="0">
			    <tr>
		                <td align="right" nowrap="nowrap"><div id="do_date_div"><?php echo $AppUI->_('Date'); ?>:&nbsp;</div></td>
		                <td nowrap="nowrap" width="1%">
		                    <input type='hidden' id='deleg_do_date' name='deleg_do_date' value='<?php echo $deleg_date ? $deleg_date->format(FMT_TIMESTAMP_DATE) : ''; ?>' />
		                    <input type='text' onchange="setDate_new('delegateFrm', 'do_date');" class='text' style='width:120px;' id='do_date' name='do_date' value='<?php echo $deleg_date ? $deleg_date->format($df) : ''; ?>' />
		                    <a onclick="return showCalendar('do_date', '<?php echo $df ?>', 'delegateFrm', null, true, true)" href="javascript: void(0);">
		                        <img style="vertical-align: middle" src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		                    </a>
		                </td>
		                <?php
		                    echo '<td nowrap="nowrap" width="1%">&nbsp;/&nbsp;' . arraySelect($hours, 'do_hour', 'size="1" onchange="setAMPM(this)" class="text"', $deleg_date ? $deleg_date->getHour() : $start) . '</td>';
				    echo '<td nowrap="nowrap" width="1%">&nbsp;:&nbsp;</td>';
		                    echo '<td nowrap="nowrap" width="1%">' . arraySelect($minutes, 'do_minute', 'size="1" class="text"', $deleg_date ? $deleg_date->getMinute() : '00') . '</td>';
		                    if (stristr($AppUI->getPref('TIMEFORMAT'), '%p')) {
		                        echo '<td nowrap="nowrap" width="1%"><input type="text" name="do_hour_ampm" id="do_hour_ampm" value="' . ($deleg_date ? $deleg_date->getAMPM() : ($start > 11 ? 'pm' : 'am')) . '" disabled="disabled" class="text" size="2" /></td>';
				    } else {
					echo '<td nowrap="nowrap" width="1%">&nbsp;</td>';
		                    }
		                ?>
				<td width="100%" colspan="2"></td>
			    </tr>
			    <tr>
		                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Name'); ?>:&nbsp;</div></td>
		                <td colspan="6"><input type="text" name="deleg_name" class="text" size="60" /></td>
	                        <td align="right"></td>
			    </tr>
			    <tr>
		                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:&nbsp;</div></td>
		                <td colspan="6"><textarea name="deleg_description" class="textarea" cols="60" rows="5"></textarea></td>
	                        <td align="right"><input type="submit" class="button" value="<?php echo $AppUI->_('Delegate tasks'); ?>" /></td>
			    </tr>
			    <tr>
		                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Delegate To'); ?>:&nbsp;</div></td>
		                <td><?php echo arraySelect($user_list, 'user_id', 'size="1" class="text"', 1, false); ?></td>
				<td width="100%" colspan="6"></td>
			    </tr>
			</table>
		    </div></td>	
	    	</tr>
	    </table>
	</td></tr>
    </table>
    <table class="std" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="tabox" colspan="20">
	    <table width="100%"><tr>
		<td nowrap="nowrap"><?php echo $AppUI->_('Key'); ?>:</td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffffff">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Future Task'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#e6eedd">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Started and on time'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#ffeebb">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Should have started'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#CC6666">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Overdue'); ?></td>
		<td>&nbsp;</td>
		<td style="border-style:solid;border-width:1px" bgcolor="#aaddaa">&nbsp;&nbsp;</td>
		<td nowrap="nowrap">=<?php echo $AppUI->_('Done'); ?></td>
		<td width="40%">&nbsp;</td>
	    </tr></table>
    	</td></tr>
    </table>
</form>
