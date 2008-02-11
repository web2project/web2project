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
$log_all = w2PgetParam($_POST, 'log_all', true);
$log_all_projects = w2PgetParam($_POST, 'log_all_projects', true);
$use_period = w2PgetParam($_POST, 'use_period', 0);
$show_orphaned = w2PgetParam($_POST, 'show_orphaned', 0);
$display_week_hours = w2PgetParam($_POST, 'display_week_hours', 0);
$max_levels = w2PgetParam($_POST, 'max_levels', 'max');
$log_userfilter = w2PgetParam($_POST, 'log_userfilter', '');
$company_id = w2PgetParam($_POST, 'company_id', 'all');
$project_id = w2PgetParam($_POST, 'project_id', 'all');

// get CProject() to filter tasks by company
require_once ($AppUI->getModuleClass('projects'));
$proj = new CProject();
// filtering by companies
// get the list of visible companies
$extra = array('join' => 'project_departments', 'on' => 'projects.project_id = project_departments.project_id');
$projects = $proj->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$projFilter = arrayMerge(array('all' => $AppUI->_('All Projects')), $projects);

$durnTypes = w2PgetSysVal('TaskDurationType');
$taskPriority = w2PgetSysVal('TaskPriority');

$table_header = '';
$table_rows = '';

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new CDate($log_start_date) : new CDate();
$end_date = intval($log_end_date) ? new CDate($log_end_date) : new CDate();
$now = new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);
?>

<script language="javascript">
function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
}
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
<form name="editFrm" action="index.php?m=tasks&a=tasksperuser" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		<a href="#" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td nowrap="nowrap">
                <?php
$system_users = w2PgetUsers();
?>
                <?php echo arraySelect($system_users, 'log_userfilter', 'class="text" style="width: 200px"', $log_userfilter); ?>
	</td>
	<td nowrap="nowrap">
                <!-- // not in use anymore <input type="checkbox" name="log_all_projects" <?php if ($log_all_projects)
	echo "checked" ?> >
		<?php /* echo $AppUI->_( 'Log All Projects' ); */ ?>
		</input>
		<br> -->
		<input type="checkbox" name="display_week_hours" id="display_week_hours" <?php if ($display_week_hours)
		echo 'checked="checked"' ?> />
		<label for="display_week_hours"><?php echo $AppUI->_('Display allocated hours/week'); ?></label>
		<br />
		<input type="checkbox" name="use_period" id="use_period" <?php if ($use_period)
	echo 'checked="checked"' ?> />
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
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="#" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
         <td nowrap="nowrap" colspan="1" align="left"><?php echo $AppUI->_('Projects'); ?>:
                <?php echo arraySelect($projFilter, 'project_id', 'size=1 class=text', $project_id, false); ?>
	</td>
        <td><input type="checkbox" name="show_orphaned" id="show_orphaned" <?php if ($show_orphaned)
	echo 'checked="checked"' ?> />
		<label for="show_orphaned"><?php echo $AppUI->_('Hide orphaned tasks'); ?></label>
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
?>
<?php
if ($do_report) {
	// get Users with all Allocation info (e.g. their freeCapacity)
	$tempoTask = new CTask();
	$userAlloc = $tempoTask->getAllocation('user_id');

	// Let's figure out which users we have
	$user_list = w2PgetUsersHashList();

	$ss = '"' . $start_date->format(FMT_DATETIME_MYSQL) . '"';
	$se = '"' . $end_date->format(FMT_DATETIME_MYSQL) . '"';

	$and = false;
	$where = false;

	$q = new DBQuery;
	$q->addTable('tasks', 't');
	$q->addQuery('t.*');
	$q->addJoin('projects', 'pr', 'pr.project_id = t.task_project', 'inner');

	if ($use_period) {
		$q->addWhere('(( task_start_date >= ' . $ss . ' AND task_start_date <= ' . $se . ' ) OR ' . '  ( task_end_date <= ' . $se . ' AND task_end_date >= ' . $ss . ' ))');
	}
	$q->addWhere('(task_percent_complete < 100)');

	if ($project_id != 'all') {
		$q->addWhere('t.task_project="' . $project_id . '"');
	}

	if ($company_id != 'all') {
		$q->addWhere('p.project_company="' . $company_id . '"');
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
		$task->bind($task_data);
		$task_users = $task->getAssignedUsers();
		foreach (array_keys($task_users) as $key => $uid) {
			$user_assigned_tasks[$uid][] = $task_id;
		}
		$task->task_assigned_users = $task_users;
		$task_list[$i] = $task;

		$i += 1;
	}
	$Ntasks = $i;

	//for($i=0;$i<$Ntasks;$i++) {
	//print $task_list[$i]->task_name."<br>\n";
	//}

	$user_usage = array();
	$task_dates = array();

	$actual_date = $start_date;
	$days_header = ''; // we will save days title here

	if (strtolower($max_levels) == 'max') {
		$max_levels = -1;
	} elseif ($max_levels == '') {
		$max_levels = -1;
	} else {
		$max_levels = atoi($max_levels);
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
		if (!$use_period) {
			$sss = -1;
			$sse = -1;
		}
		if ($display_week_hours and !$use_period) {
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

		$table_header = '<tr>' . '<th nowrap="nowrap"></th>' . '<th nowrap="nowrap">' . $AppUI->_('P') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('Task') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('Proj.') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('Duration') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('Start Date') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('End[d]') . '</th>' . weekDates($display_week_hours, $sss, $sse) . '<th nowrap="nowrap">' . $AppUI->_('Current Assignees') . '</th>' . '<th nowrap="nowrap">' . $AppUI->_('Possible Assignees') . '</th></tr>';
		$table_rows = '';

		foreach ($user_list as $user_id => $user_data) {
			// count tasks per user;
			$z = 0;
			foreach ($task_list as $task) {
				if (isMemberOfTask($task_list, $Ntasks, $user_id, $task)) {
					$z++;
				}
			}

			$tmpuser = '<form name="assFrm' . $user_id . '" action="index.php?m=tasks&a=tasksperuser" method="post">
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
            <a href="javascript:chAssignment(' . $user_id . ', 0, 1);">' . w2PshowImage('remove.png', 16, 16, 'Unassign User', 'Unassign User from Task', 'tasks') . '</a>&nbsp;' . '<a href="javascript:chAssignment(' . $user_id . ', 1, 0);">' . w2PshowImage('exchange.png', 24, 16, 'Hand Over', 'Unassign User from Task and handing-over to selected Users', 'tasks') . '</a>&nbsp;' . '<a href="javascript:chAssignment(' . $user_id . ', 0, 0);">' . w2PshowImage('add.png', 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks', 'tasks') . '</a></td>';
			$tmpuser .= '<td align="center"><select class="text" name="percentage_assignment" title="' . $AppUI->_('Assign with Percentage') . '">';
			for ($i = 0; $i <= 100; $i += 5) {
				$tmpuser .= '<option ' . (($i == 30) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>';
			}
			$tmpuser .= '</select></td>';
			$tmpuser .= '<td align="center">' . arraySelect($taskPriority, 'user_task_priority', 'onchange="javascript:chPriority(' . $user_id . ');" size="1" class="text" title="' . $AppUI->_('Change User specific Task Priority of selected Tasks') . '"', 0, true);
			$tmpuser .= '</td></tr></table></td>';

			$tmpuser .= '</tr>';

			$tmptasks = '';
			$actual_date = $start_date;

			$zi = 0;
			foreach ($task_list as $task) {
				//if (!isChildTask($task)) {
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
				//}
			}
			if ($tmptasks != '') {
				$table_rows .= $tmpuser;
				$table_rows .= $tmptasks . '</form>';
			}
		}
	}
}

function doChildren($list, $N, $id, $uid, $level, $maxlevels, $display_week_hours, $ss, $se) {
	$tmp = '';
	if ($maxlevels == -1 || $level < $maxlevels) {
		for ($c = 0; $c < $N; $c++) {
			$task = $list[$c];
			if (($task->task_parent == $id) and isChildTask($task)) {
				// we have a child, do we have the user as a member?
				if (isMemberOfTask($list, $N, $uid, $task)) {
					$tmp .= displayTask($list, $task, $level, $display_week_hours, $ss, $se, $uid);
					$tmp .= doChildren($list, $N, $task->task_id, $uid, $level + 1, $maxlevels, $display_week_hours, $ss, $se);
				}
			}
		}
	}
	return $tmp;
}

function isMemberOfTask($list, $N, $user_id, $task) {

	global $user_assigned_tasks;

	if (isset($user_assigned_tasks[$user_id])) {
		if (in_array($task->task_id, $user_assigned_tasks[$user_id]))
			return true;
		// else if ( $task->task_id != $task->task_parent
		//&& in_array($task->task_parent, $user_assigned_tasks[$user_id]))
		//	return true;
	}
	return false;
}

function displayTask($list, $task, $level, $display_week_hours, $fromPeriod, $toPeriod, $user_id) {

	global $AppUI, $df, $durnTypes, $log_userfilter_users, $now, $priority, $system_users, $z, $zi, $x, $userAlloc, $projects;
	//if the user has no permission to the project don't show the tasks
	if (!(key_exists($task->task_project, $projects))) {
		return;
	}

	$zi++;
	$users = $task->task_assigned_users;
	$task->userPriority = $task->getUserSpecificTaskPriority($user_id);
	$project = $task->getProject();
	$tmp = '<tr>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	$tmp .= '<input type="checkbox" name="selected_task[' . $task->task_id . ']" value="' . $task->task_id . '" />';
	$tmp .= '</td>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	if ($task->userPriority < 0) {
		$tmp .= '<img src="' . w2PfindImage('icons/priority-' . -$task->userPriority . '.gif') . '" width="13" height="16">';
	} elseif ($task->userPriority > 0) {
		$tmp .= '<img src="' . w2PfindImage('icons/priority+' . $task->userPriority . '.gif') . '" width="13" height="16">';
	}
	$tmp .= '</td>';
	$tmp .= '<td>';

	for ($i = 0; $i < $level; $i++) {
		$tmp .= '&#160';
	}

	if ($task->task_milestone == true) {
		$tmp .= '<b>';
	}
	if ($level >= 1) {
		$tmp .= w2PshowImage('corner-dots.gif', 16, 12, 'Subtask', '', 'tasks') . '&nbsp;';
	}
	$tmp .= '<a href="?m=tasks&a=view&task_id=' . $task->task_id . '">' . $task->task_name . '</a>';
	if ($task->task_milestone == true) {
		$tmp .= '</b>';
	}
	if ($task->task_priority < 0) {
		$tmp .= '&nbsp;(<img src="' . w2PfindImage('icons/priority-' . -$task->task_priority . '.gif') . '" width="13" height="16" />)';
	} elseif ($task->task_priority > 0) {
		$tmp .= '&nbsp;(<img src="' . w2PfindImage('icons/priority+' . $task->task_priority . '.gif') . '" width="13" height="16" />)';
	}
	$tmp .= '</td>';
	$tmp .= '<td align="left">';
	$tmp .= '<a href="?m=projects&a=view&project_id=' . $task->task_project . '" style="background-color:#' . @$project['project_color_identifier'] . '; color:' . bestColor(@$project['project_color_identifier']) . '">' . $project['project_short_name'] . '</a>';
	$tmp .= '</td>';
	$tmp .= '<td align="right" nowrap="nowrap">';
	$tmp .= $task->task_duration . '&nbsp;' . substr($AppUI->_($durnTypes[$task->task_duration_type]),0,1);
	$tmp .= '</td>';
	$tmp .= '<td align="center" nowrap="nowrap">';
	$dt = new CDate($task->task_start_date);
	$tmp .= $dt->format($df);
	$tmp .= '&#160&#160&#160</td>';
	$tmp .= '<td align="right" nowrap="nowrap">';
	$ed = new CDate($task->task_end_date);
	$dt = $now->dateDiff($ed);
	$sgn = $now->compare($ed, $now);
	$tmp .= ($dt * $sgn);
	$tmp .= '</td>';
	if ($display_week_hours) {
		$tmp .= displayWeeks($list, $task, $level, $fromPeriod, $toPeriod);
	}
	$tmp .= '<td>';
	$sep = $us = '';
	foreach ($users as $key => $row) {
		if ($row['user_id']) {
			$us .= '<a href="?m=admin&a=viewuser&user_id=' . $row[0] . '">' . $sep . $row['contact_first_name'] . '&nbsp;' . $row['contact_last_name'] . '&nbsp;(' . $row['perc_assignment'] . '%)</a>';
			$sep = ', ';
		}
	}
	$tmp .= $us;
	$tmp .= '</td>';
	// create the list of possible assignees
	if ($zi == 1) {
		//  selectbox may not have a size smaller than 2, use 5 here as minimum
		$zz = ($z < 5) ? 5 : ($z * 1.5);
		if (sizeof($users) >= 7) {
			$zz = $zz * 2;
		}
		$zm1 = $z - 2;
		if ($zm1 == 0)
			$zm1 = 1;
		$assUser = $userAlloc[$user_id]['userFC'];
		if ($user_id == 0) { // need to handle orphaned tasks different from tasks with existing assignees
			$zm1++;
		}
		$tmp .= '<td valign="top" align="center" nowrap="nowrap">';
		$tmp .= '<select name="add_users" style="width:200px" size="10" class="text" multiple="multiple" ondblclick="javascript:chAssignment(' . $user_id . ', 0, false)">';
		foreach ($userAlloc as $v => $u) {
			$tmp .= '<option value="' . $u['user_id'] . '">' . w2PformSafe($u['userFC']) . '</option>';
		}
		$tmp .= '</select>';
		//$tmp.= arraySelect( $user_list, 'add_users', 'class="text" STYLE="width: 200px" size="'.($zz-1).'" multiple="multiple"',NULL );
		$tmp .= '</td>';
	}

	$tmp .= '</tr>';
	return $tmp;
}

function isChildTask($task) {
	return $task->task_id != $task->task_parent;
}

function atoi($a) {
	return $a + 0;
}

function weekDates($display_allocated_hours, $fromPeriod, $toPeriod) {
	if ($fromPeriod == -1) {
		return '';
	}
	if (!$display_allocated_hours) {
		return '';
	}

	$s = new CDate($fromPeriod);
	$e = new CDate($toPeriod);
	$sw = getBeginWeek($s);
	//$ew=getEndWeek($e); //intval($e->Format('%U'));
	$dw = ceil($e->dateDiff($s) / 7);
	$ew = $sw + $dw;
	$row = '';
	for ($i = $sw; $i <= $ew; $i++) {
		$wn = $s->getWeekofYear() % 52;
		$wn = ($wn != 0) ? $wn : 52;

		$row .= '<th title="' . $s->getYear() . '" nowrap="nowrap">' . $wn . '</th>';
		$s->addSeconds(168 * 3600); // + one week
	}
	return $row;
}

function weekCells($display_allocated_hours, $fromPeriod, $toPeriod) {

	if ($fromPeriod == -1) {
		return 0;
	}
	if (!$display_allocated_hours) {
		return 0;
	}

	$s = new CDate($fromPeriod);
	$e = new CDate($toPeriod);
	$sw = getBeginWeek($s); //intval($s->Format('%U'));
	//$ew=getEndWeek($e); //intval($e->Format('%U'));
	$dw = ceil($e->dateDiff($s) / 7);
	$ew = $sw + $dw;

	return $ew - $sw + 1;
}

// Look for a user when he/she has been allocated
// to this task and when. Report this in weeks
// This function is called within 'displayTask()'
function displayWeeks($list, $task, $level, $fromPeriod, $toPeriod) {

	if ($fromPeriod == -1) {
		return '';
	}

	$s = new CDate($fromPeriod);
	$e = new CDate($toPeriod);
	$sw = getBeginWeek($s); //intval($s->Format('%U'));
	//$ew=getEndWeek($e); //intval($e->Format('%U'));
	$dw = ceil($e->dateDiff($s) / 7);
	$ew = $sw + $dw;

	$st = new CDate($task->task_start_date);
	$et = new CDate($task->task_end_date);
	$stw = getBeginWeek($st); //intval($st->Format('%U'));
	//$etw=getEndWeek($et); //intval($et->Format('%U'));
	$dtw = ceil($et->dateDiff($st) / 7);
	$etw = $stw + $dtw;

	//print "week from: $stw, to: $etw<br>\n";

	$row = '';
	for ($i = $sw; $i <= $ew; $i++) {
		if ($i >= $stw and $i < $etw) {
			$color = 'blue';
			if ($level == 0 and hasChildren($list, $task)) {
				$color = '#C0C0FF';
			} elseif ($level == 1 and hasChildren($list, $task)) {
				$color = '#9090FF';
			}
			$row .= '<td  nowrap="nowrap" bgcolor="' . $color . '">';
		} else {
			$row .= '<td nowrap="nowrap">';
		}
		$row .= '&#160&#160</td>';
	}

	return $row;
}

function getBeginWeek($d) {
	$dn = intval($d->Format('%w'));
	$dd = new CDate($d);
	$dd->subtractSeconds($dn * 24 * 3600);
	return intval($dd->Format('%U'));
}

function getEndWeek($d) {

	$dn = intval($d->Format('%w'));
	if ($dn > 0) {
		$dn = 7 - $dn;
	}
	$dd = new CDate($d);
	$dd->addSeconds($dn * 24 * 3600);
	return intval($dd->Format('%U'));
}

function hasChildren($list, $task) {
	foreach ($list as $t) {
		if ($t->task_parent == $task->task_id) {
			return true;
		}
	}
	return false;
}

?>

<center>
	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
		<?php echo $table_header . $table_rows; //show tasks with existing assignees

// show orphaned tasks
if (!$show_orphaned) {
	$user_id = 0; //reset user id to zero (create new object - no user)
	$tmpuser = '<form name="assFrm' . $user_id . '" action="index.php?m=tasks&a=tasksperuser" method="post">
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

	function getOrphanedTasks($tval) {
		return (sizeof($tval->task_assigned_users) > 0) ? null : $tval;
	}

	$orphTasks = array_diff(array_map('getOrphanedTasks', $task_list), array(null));

	$tmptasks = '';
	$actual_date = $start_date;

	$zi = 0;
	foreach ($orphTasks as $task) {
		$tmptasks .= displayTask($orphTasks, $task, 0, $display_week_hours, $sss, $sse, $user_id);
		// do we need to get the children?
		//$tmptasks.=doChildren($orphTasks,$Ntasks,$task->task_id,$user_id,1,$max_levels,$display_week_hours,$sss,$sse);
	}
	if ($tmptasks != '') {
		echo $tmpuser;
		echo $tmptasks . '</form>';
	}

} // end of show orphaned tasks

?>
	</table>
</center>