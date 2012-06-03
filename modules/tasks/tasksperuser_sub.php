<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

// check permissions
if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

$do_report = w2PgetParam($_POST, 'do_report', true);
$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = (int) w2PgetParam($_POST, 'log_all', 1);
$log_all_projects = (int) w2PgetParam($_POST, 'log_all_projects', 1);
$use_period = w2PgetParam($_POST, 'use_period', 'off');
$show_orphaned = w2PgetParam($_POST, 'show_orphaned', 'off');
$display_week_hours = w2PgetParam($_POST, 'display_week_hours', 'off');
$max_levels = w2PgetParam($_POST, 'max_levels', 'max');
$log_userfilter = (int) w2PgetParam($_POST, 'log_userfilter', -1);
$company_id = (int) w2PgetParam($_POST, 'company_id', 'all');
$project_id = (int) w2PgetParam($_POST, 'project_id', 'all');
$report_type = (int) w2PgetParam($_POST, 'report_type', '');

// get CProject() to filter tasks by company
$proj = new CProject();
// filtering by companies
// get the list of visible companies
$extra = array('join' => 'project_departments', 'on' => 'projects.project_id = project_departments.project_id');
$projects = $proj->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projFilter = arrayMerge(array('all' => $AppUI->_('All Projects')), $projects);

$durnTypes = w2PgetSysVal('TaskDurationType');
$taskPriority = w2PgetSysVal('TaskPriority');
$taskPriority[999]=$AppUI->_('Select User Priority');

$table_header = '';
$table_rows = '';

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();
$now = new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

// get Users with all Allocation info (e.g. their freeCapacity)
$tempoTask = new CTask();
$userAlloc = $tempoTask->getAllocation('user_id', null, true);
?>

<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function checkAll(user_id) {
        var f = eval( 'document.assFrm' + user_id );
        var cFlag = f.master.checked ? false : true;

        for (var i=0, i_cmp=f.elements.length; i<i_cmp;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == 'checkbox' && e.checked == cFlag && e.name != 'master')
                {
                         e.checked = !e.checked;
                }
        }
}


function chAssignment(user_id, rmUser, del) {
        var f = eval( 'document.assFrm' + user_id );
        var fl = f.add_users.length-1;
        var c = 0;
        var a = 0;

        f.hassign.value = '';
        f.htasks.value = '';

        // harvest all checked checkboxes (tasks to process)
        for (var i=0, i_cmp=f.elements.length; i<i_cmp; i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == 'checkbox' && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +','+ e.value;
                }
        }

        // harvest all selected possible User Assignees
        for (fl; fl > -1; fl--){
                if (f.add_users.options[fl].selected) {
                        a++;
                        f.hassign.value = ',' + f.hassign.value +','+ f.add_users.options[fl].value;
                }
        }

        if (del == true) {
                        if (c == 0) {
                                 alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                        } else {
                                if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>' )) {
                                        f.del.value = 1;
                                        f.rm.value = rmUser;
                                        f.user_id.value = user_id;
                                        f.submit();
                                }
                        }
        } else {

                if (c == 0) {
                        alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                } else {

                        if (a == 0) {
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                f.rm.value = rmUser;
                                f.del.value = del;
                                f.user_id.value = user_id;
                                f.submit();

                        }
                }
        }
}

function chPriority(user_id) {
        var f = eval( 'document.assFrm' + user_id );
        var c = 0;

        f.htasks.value = '';

        // harvest all checked checkboxes (tasks to process)
        for (var i=0, i_cmp=f.elements.length; i<i_cmp; i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == 'checkbox' && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +','+ e.value;
                }
        }

        if (c == 0) {
                alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
        } else {
                f.rm.value = 0;
                f.del.value = 0;
                f.store.value = 0;
		f.chUTP.value = 1;
                f.user_id.value = user_id;
                f.submit();
        }
}
<?php } ?>
</script>
<form name="editFrm" action="index.php?m=tasks&amp;a=tasksperuser" method="post" accept-charset="utf-8">
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />
	
	<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
			<td nowrap="nowrap">
				<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
					<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
				</a>
			</td>
			<td nowrap="nowrap">
				<?php
                    $active_users = $perms->getPermittedUsers('tasks');
                    $system_users = arrayMerge(array('-1' => $AppUI->_('All Users')), $active_users);
                    echo arraySelect($system_users, 'log_userfilter', 'class="text" style="width: 200px"', $log_userfilter);
                ?>
			</td>
			<td nowrap="nowrap">
				<input type="checkbox" name="display_week_hours" id="display_week_hours" <?php if ('on' == $display_week_hours) { echo 'checked="checked"'; } ?> />
				<label for="display_week_hours"><?php echo $AppUI->_('Display allocated hours/week'); ?></label><br />
				<input type="checkbox" name="use_period" id="use_period" <?php if ('on' == $use_period) { echo 'checked="checked"'; } ?> />
				<label for="use_period"><?php echo $AppUI->_('Use the period'); ?></label>
			</td>
			<td align="left" width="50%" nowrap="nowrap">
				<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?>:</td>
			<td>
				<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
					<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
				</a>
			</td>
			<td nowrap="nowrap" colspan="1" align="left"><?php echo $AppUI->_('Projects'); ?>:
				<?php echo arraySelect($projFilter, 'project_id', 'size=1 class=text', $project_id, false); ?>
			</td>
		    <td>
				<input type="checkbox" name="show_orphaned" id="show_orphaned" <?php if ($show_orphaned == 'on') { echo 'checked="checked"'; } ?> />
				<label for="show_orphaned"><?php echo $AppUI->_('Show orphaned tasks'); ?></label>
			</td>
			<td>
				<?php echo $AppUI->_('Levels to display'); ?>
				<input type="text" name="max_levels" size="10" maxlength="3" value="<?php echo $max_levels; ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="5" align="left" nowrap="nowrap"><?php echo $AppUI->_('P') . "&nbsp;=&nbsp;" . $AppUI->_('User specific Task Priority'); ?></td>
		</tr>
	</table>
	<?php
		if (function_exists('styleRenderBoxBottom')) {
			echo styleRenderBoxBottom();
		}
	?>
</form>
<br />
<?php
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}

if ($do_report) {
	// Let's figure out which users we have
	$user_list = $active_users;

	$ss = '\'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'';
	$se = '\'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'';

	$and = false;
	$where = false;

	$q = new w2p_Database_Query;
	$q->addTable('tasks', 't');
	$q->addQuery('t.*');
	$q->addJoin('projects', 'pr', 'pr.project_id = t.task_project', 'inner');
	$q->addWhere('pr.project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('pr.project_status <> ' . (int)$template_status);
	}

	if ('on' == $use_period) {
		$q->addWhere('(( task_start_date >= ' . $ss . ' AND task_start_date <= ' . $se . ' ) OR ' . '  ( task_end_date <= ' . $se . ' AND task_end_date >= ' . $ss . ' ))');
	}
	$q->addWhere('(task_percent_complete < 100)');

    $q->addJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
    if ($log_userfilter > -1) {
        $q->addWhere('ut.user_id = '.$log_userfilter);
    }

	if ($project_id != 'all') {
		$q->addWhere('t.task_project=' . (int)$project_id);
	}

	if ($company_id != 'all') {
		$q->addWhere('pr.project_company = ' . (int)$company_id);
	}

	$q->addOrder('task_project');
	$q->addOrder('task_end_date');
	$q->addOrder('task_start_date');
	$proj->setAllowedSQL($AppUI->user_id, $q, null, 'pr');

	$task_list_hash = $q->loadHashList('task_id');

	$q->clear();
	$task_list = array();
	$task_assigned_users = array();
	$user_assigned_tasks = array();
	$i = 0;

	foreach ($task_list_hash as $task_id => $task_data) {
		$task = new CTask();
		$task->load($task_id);
		$task_users = $task->getAssignedUsers($task_id);
		foreach (array_keys($task_users) as $key => $uid) {
			$user_assigned_tasks[$uid][] = $task_id;
		}
		$task->task_assigned_users = $task_users;
		$task_list[$i] = $task;

		$i += 1;
	}
	$Ntasks = $i;

	$user_usage = array();
	$task_dates = array();

	$actual_date = $start_date;
	$days_header = ''; // we will save days title here

	if (strtolower($max_levels) == 'max') {
		$max_levels = -1;
	} elseif ($max_levels == '') {
		$max_levels = -1;
	} else {
		$max_levels = (int) $max_levels;
	}
	if ($max_levels == 0) {
		$max_levels = 1;
	}
	if ($max_levels < 0) {
		$max_levels = -1;
	}

	if (count($task_list) == 0) {
		echo '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">';
		echo '<tr>' . '<td nowrap="nowrap">' . $AppUI->_('No data available') . '</td></tr>';
		echo '</table>';
	} else {

		$sss = $ss;
		$sse = $se;
		if ('on' != $use_period) {
			$sss = -1;
			$sse = -1;
		}
		if ('on' == $display_week_hours && ('on' != $use_period)) {
			foreach ($task_list as $t) {
				if ($sss == -1) {
					$sss = $t->task_start_date;
					$sse = $t->task_end_date;
				} else {
					if ($t->task_start_date < $sss) {
						$sss = $t->task_start_date;
					}
					if ($t->task_end_date > $sse) {
						$sse = $t->task_end_date;
					}
				}
			}
		}

		$table_header = '<tr>' . '<th nowrap="nowrap"></th>' .
            '<th nowrap="nowrap">' . $AppUI->_('P') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('Task') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('Proj.') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('Duration') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('Start Date') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('End[d]') . '</th>' . weekDates($display_week_hours, $sss, $sse) .
            '<th nowrap="nowrap">' . $AppUI->_('Current Assignees') . '</th>' .
            '<th nowrap="nowrap">' . $AppUI->_('Possible Assignees') . '</th></tr>';
		$table_rows = '';

		foreach ($user_list as $user_id => $user_data) {
			if ($log_userfilter == -1 || ($user_id == $log_userfilter)) {
                $z = 0;
                foreach ($task_list as $task) {
                    if (isMemberOfTask($task_list, $Ntasks, $user_id, $task)) {
                        $z++;
                    }
                }
                $tmpuser = '<form name="assFrm' . $user_id . '" action="index.php?m=tasks&amp;a=tasksperuser" method="post" accept-charset="utf-8">
                        <input type="hidden" name="chUTP" value="0" />
              <input type="hidden" name="del" value="1" />
              <input type="hidden" name="rm" value="0" />
              <input type="hidden" name="store" value="0" />
              <input type="hidden" name="dosql" value="do_task_assign_aed" />
              <input type="hidden" name="user_id" value="' . $user_id . '" />
              <input type="hidden" name="hassign" />
              <input type="hidden" name="htasks" />
              <tr>
              <td align="center" bgcolor="#D0D0D0"><input onclick="javascript:checkAll(' . $user_id . ');" type="checkbox" name="master" value="true"/></td>
              <td colspan="2" align="left" nowrap="nowrap" bgcolor="#D0D0D0">
              <font color="black">
              <b><a href="index.php?m=calendar&a=day_view&user_id=' . $user_id . '&tab=1">' . $userAlloc[$user_id]['userFC'] . '</a></b></font></td>';
                for ($w = 0; $w <= (4 + weekCells($display_week_hours, $sss, $sse)); $w++) {
                    $tmpuser .= '<td bgcolor="#D0D0D0"></td>';
                }

                $tmpuser .= '<td bgcolor="#D0D0D0"><table width="100%"><tr>';
                $tmpuser .= '<td align="left">
					 <a href="javascript:chAssignment(' . $user_id . ', 0, 1);"><img src="' . w2PfindImage('remove.png', 'tasks')   . '" border="0" alt="' . $AppUI->_('Unassign User') . '" title="' . $AppUI->_('Unassign User from Task') . '" /></a>&nbsp;' .
					'<a href="javascript:chAssignment(' . $user_id . ', 1, 0);"><img src="' . w2PfindImage('exchange.png', 'tasks') . '" border="0" alt="' . $AppUI->_('Hand Over') . '" title="' . $AppUI->_('Unassign User from Task and assign to selected Users') . '" /></a>&nbsp;' .
					'<a href="javascript:chAssignment(' . $user_id . ', 0, 0);"><img src="' . w2PfindImage('add.png', 'tasks')      . '" border="0" alt="' . $AppUI->_('Assign Users') . '" title="' . $AppUI->_('Assign selected Users to selected Tasks') . '" /></a></td>';
                $tmpuser .= '<td align="center"><select class="text" name="percentage_assignment" title="' . $AppUI->_('Assign with Percentage') . '">';
                for ($i = 0; $i <= 100; $i += 5) {
                    $tmpuser .= '<option ' . (($i == 30) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>';
                }
                $tmpuser .= '</select></td>';
                $tmpuser .= '<td align="center">' . arraySelect($taskPriority, 'user_task_priority', 'onchange="javascript:chPriority(' . $user_id . ');" size="1" class="text" title="' . $AppUI->_('Change User specific Task Priority of selected Tasks') . '"', 999, true);
                $tmpuser .= '</td></tr></table></td>';

                $tmpuser .= '</tr>';

                $tmptasks = '';
                $actual_date = $start_date;

                $zi = 0;
                foreach ($task_list as $task) {
                    if (isMemberOfTask($task_list, $Ntasks, $user_id, $task)) {
                        $tmptasks .= displayTask($task_list, $task, 0, $display_week_hours, $sss, $sse, $user_id);
                        // Get children
                        $tmptasks .= doChildren($task_list, $Ntasks, $task->task_id, $user_id, 1, $max_levels, $display_week_hours, $sss, $sse);
                    } else {
                        // we have to process children task the user
                        // is member of, but member of their parent task.
                        // Also show the parent task then before the children.
                        $tmpChild = '';
                        $tmpChild = doChildren($task_list, $Ntasks, $task->task_id, $user_id, 1, $max_levels, $display_week_hours, $sss, $sse);
                        if ($tmpChild > '') {
                            $tmptasks .= displayTask($task_list, $task, 0, $display_week_hours, $sss, $sse, $user_id);
                            $tmptasks .= $tmpChild;
                        }
                    }
                }
                if ($tmptasks != '') {
                    $table_rows .= $tmpuser;
                    $table_rows .= $tmptasks . '</form>';
                }
            }
		}
	}
}

?>

<center>
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
		<?php echo $table_header . $table_rows; //show tasks with existing assignees

// show orphaned tasks
if ($show_orphaned == 'on') {
    $user_id = 0; //reset user id to zero (create new object - no user)
	$tmpuser = '<form name="assFrm' . $user_id . '" action="index.php?m=tasks&a=tasksperuser" method="post" accept-charset="utf-8">
				<input type="hidden" name="del" value="1" />
				<input type="hidden" name="rm" value="0" />
				<input type="hidden" name="store" value="0" />
				<input type="hidden" name="dosql" value="do_task_assign_aed" />
				<input type="hidden" name="user_id" value="' . $user_id . '" />
				<input type="hidden" name="hassign" />
				<input type="hidden" name="htasks" />
				<tr>';
	$tmpuser .= '<td bgcolor="#D0D0D0"><input onclick="javascript:checkAll(' . $user_id . ');" type="checkbox" name="master" value="true"/></td>
				<td colspan="2" align="left" nowrap="nowrap" bgcolor="#D0D0D0">
				<font color="black">
				<b><a href="index.php?m=calendar&a=day_view&user_id=' . $user_id . '&tab=1">' . $AppUI->_('Orphaned Tasks') . '</a></b></font></td>';

	for ($w = 0; $w <= (4 + weekCells($display_week_hours, $sss, $sse)); $w++) {
		$tmpuser .= '<td bgcolor="#D0D0D0"></td>';
	}
	$tmpuser .= '<td bgcolor="#D0D0D0"><table width="100%"><tr>';
	$tmpuser .= '<td align="left">' . '<a href="javascript:chAssignment(' . $user_id . ', 0, 0);">' . w2PshowImage('add.png', 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks', 'tasks') . '</a></td>';
	$tmpuser .= '<td align="center"><select class="text" name="percentage_assignment" title="' . $AppUI->_('Assign with Percentage') . '">';
	for ($i = 0; $i <= 100; $i += 5) {
		$tmpuser .= '<option ' . (($i == 30) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>';
	}
	$tmpuser .= '</select></td>';
	$tmpuser .= '<td align="center">' . arraySelect($taskPriority, 'task_priority', 'onchange="javascript:chPriority(' . $user_id . ');" size="1" class="text" title="' . $AppUI->_('Change Priority of selected Tasks') . '"', 0, true);
	$tmpuser .= '</td></tr></table></td>';

	$tmpuser .= '</tr>';

	$orphTasks = array_diff(array_map('getOrphanedTasks', $task_list), array(null));

	$tmptasks = '';
	$actual_date = $start_date;

	$zi = 0;
	foreach ($orphTasks as $task) {
		$tmptasks .= displayTask($orphTasks, $task, 0, $display_week_hours, $sss, $sse, $user_id);
	}
	if ($tmptasks != '') {
		echo $tmpuser;
		echo $tmptasks . '</form>';
	}

} // end of show orphaned tasks

?>
	</table>
</center>