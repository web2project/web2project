<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes;
global $task_sort_item1, $task_sort_type1, $task_sort_order1;
global $task_sort_item2, $task_sort_type2, $task_sort_order2;
global $user_id, $w2Pconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
global $history_active;

/*
tasks.php

This file contains common task list rendering code used by
modules/tasks/index.php and modules/projects/vw_tasks.php

in

External used variables:
* $min_view: hide some elements when active (used in the vw_tasks.php)
* $project_id
* $f
* $query_string
*/

if (empty($query_string)) {
	$query_string = '?m=' . $m . '&amp;a=' . $a;
}
$canViewTask = canView('tasks');;
if (!$canViewTask) {
    $AppUI->setMsg("You are not allowed to view tasks", UI_MSG_ERROR);
    $AppUI->redirect(ACCESS_DENIED);
}

$mods = $AppUI->getActiveModules();
$history_active = !empty($mods['history']) && canView('history');

/*
* Let's figure out which tasks are selected
*/
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);

$pinned_only = (int) w2PgetParam($_GET, 'pinned', 0);
__extract_from_tasks_pinning($AppUI, $task_id);

$durnTypes = w2PgetSysVal('TaskDurationType');
$taskPriority = w2PgetSysVal('TaskPriority');

$task_project = (int) w2PgetParam($_GET, 'task_project', null);

$task_sort_item1 = w2PgetParam($_GET, 'task_sort_item1', 'task_start_date');
$task_sort_type1 = w2PgetParam($_GET, 'task_sort_type1', '');
$task_sort_item2 = w2PgetParam($_GET, 'task_sort_item2', 'task_end_date');
$task_sort_type2 = w2PgetParam($_GET, 'task_sort_type2', '');
$task_sort_order1 = (int) w2PgetParam($_GET, 'task_sort_order1', 0);
$task_sort_order2 = (int) w2PgetParam($_GET, 'task_sort_order2', 0);
if (isset($_POST['show_task_options'])) {
	$AppUI->setState('TaskListShowIncomplete', w2PgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

$project = new CProject;
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'p.project_id');

$where_list = (count($allowedProjects)) ? implode(' AND ', $allowedProjects) : '';

$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$projects = __extract_from_tasks4($where_list, $project_id, $task_id);
$subquery = __extract_from_tasks1();
$task_status = __extract_from_tasks($min_view, $currentTabId, $project_id, $currentTabName, $AppUI);

$q = new w2p_Database_Query;
$q = __extract_from_tasks5($q, $subquery);
$q = __extract_from_tasks6($q, $history_active);

$q->addJoin('projects', 'p', 'p.project_id = task_project', 'inner');
$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
$q->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
$q->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . (int)$AppUI->user_id);

if ($project_id) {
	$q->addWhere('task_project = ' . (int)$project_id);
	//if we are on a project context make sure we show all tasks
	$f = 'all';
} else { 
	$q->addWhere('project_active = 1');
	if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
		$q->addWhere('project_status <> ' . $template_status);
	}
}

if ($pinned_only) {
	$q->addWhere('task_pinned = 1');
}

$q = __extract_from_tasks3($f, $q, $user_id, $task_id, $AppUI);

if ($showIncomplete) {
	$q->addWhere('( task_percent_complete < 100 OR task_percent_complete IS NULL)');
}

//When in task view context show all the tasks, active and inactive. (by not limiting the query by task status)
//When in a project view or in the tasks list, show the active or the inactive tasks depending on the selected tab or button.
if (!$task_id) {
	$q->addWhere('task_status = ' . (int)$task_status);
}
if (isset($task_type) && (int) $task_type > 0) {
	$q->addWhere('task_type = ' . (int)$task_type);
}
if (isset($task_owner) && (int) $task_owner > 0) {
	$q->addWhere('task_owner = ' . (int)$task_owner);
}

if (($project_id || !$task_id) && !$min_view) {
	if ($search_text = $AppUI->getState('tasks_search_string')) {
		$q->addWhere('( task_name LIKE (\'%' . $search_text . '%\') OR task_description LIKE (\'%' . $search_text . '%\') )');
	}
}

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering
$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'task_project');
if (count($allowedProjects)) {
	$q->addWhere($allowedProjects);
}

$obj = new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id, 'tasks.task_id');
if (count($allowedTasks)) {
	$q->addWhere($allowedTasks);
}

// Filter by company
if (!$min_view && $f2 != 'allcompanies') {
	$q->addJoin('companies', 'c', 'c.company_id = p.project_company', 'inner');
	$q->addWhere('company_id = ' . (int) $f2);
}

$q->addGroup('tasks.task_id');
if (!$project_id && !$task_id) {
	$q->addOrder('p.project_id, task_start_date, task_end_date');
} else {
    $q->addOrder('task_start_date, task_end_date, task_name');
}

$tasks = $q->loadList();

// POST PROCESSING TASKS
if (count($tasks) > 0) {
	foreach ($tasks as $row) {
		//add information about assigned users into the page output
        $assigned_users = __extract_from_tasks2($row);

		$row['task_assigned_users'] = $assigned_users;
	
		//pull the final task row into array
		$projects[$row['task_project']]['tasks'][] = $row;
	}
}

$showEditCheckbox = ((isset($canEdit) && $canEdit && w2PgetConfig('direct_edit_assignment')) ? true : false);
?>

<script language="javascript" type="text/javascript">
function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if (isset($canEdit) && $canEdit && $w2Pconfig['direct_edit_assignment']) { ?>
	function checkAll(project_id) {
		var f = eval('document.assFrm' + project_id);
		var cFlag = f.master.checked ? false : true;
		
		for (var i=0, i_cmp=f.elements.length; i<i_cmp;i++) {
			var e = f.elements[i];
			// only if it's a checkbox.
			if(e.type == 'checkbox' && e.checked == cFlag && e.name != 'master') {
				e.checked = !e.checked;
			}
		}
	
	}
	
	function chAssignment(project_id, rmUser, del) {
		var f = eval('document.assFrm' + project_id);
		var fl = f.add_users.length-1;
		var c = 0;
		var a = 0;
		
		f.hassign.value = '';
		f.htasks.value = '';
		
		// harvest all checked checkboxes (tasks to process)
		for (var i=0, i_cmp=f.elements.length; i<i_cmp;i++) {
			var e = f.elements[i];
			// only if it's a checkbox.
			if(e.type == 'checkbox' && e.checked == true && e.name != 'master') {
				c++;
				f.htasks.value = f.htasks.value +', '+ e.value;
			}
		}
		
		// harvest all selected possible User Assignees
		for (fl; fl > -1; fl--) {
			if (f.add_users.options[fl].selected) {
				a++;
				f.hassign.value = ', ' + f.hassign.value +', '+ f.add_users.options[fl].value;
			}
		}
		
		if (del == true) {
			if (c == 0) {
				alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
			} else if (a == 0 && rmUser == 1){
				alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
			} else if (confirm('<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>')) {
				f.del.value = 1;
				f.rm.value = rmUser;
				f.project_id.value = project_id;
				f.submit();
			}
		} else {
			
			if (c == 0) {
				alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
			} else if (a == 0) {
				alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
			} else {
				f.rm.value = rmUser;
				f.del.value = del;
				f.project_id.value = project_id;
				f.submit();
			}
		}
	}
<?php } ?>
</script>

<?php 
global $expanded;
//if we are on a task view context then all subtasks are expanded by default, on other contexts config option stands.
$expanded = $task_id ? true : $AppUI->getPref('TASKSEXPANDED');
if ($project_id) {
$open_link = w2PtoolTip($m, 'click to expand/collapse all the tasks for this project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'project_' . $project_id . '_\', \'tblProjects\',\'collapse\',0,2);" id="project_' . $project_id . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" class="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'project_' . $project_id . '_\', \'tblProjects\',\'expand\',0,2);" id="project_' . $project_id . '__expand" src="' . w2PfindImage('down22.png', $m) . '" class="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>' . w2PendTip();
?>
<form name="task_list_options" method="post" action="<?php echo $query_string; ?>" accept-charset="utf-8">
    <input type='hidden' name='show_task_options' value='1' />
    <table width='100%' border='0' cellpadding='1' cellspacing='0'>
        <tr>
          <td align='left'>
                <?php echo $open_link; ?>
          </td>
          <td align='right'>
                <table style="width: 20em;">
                    <tr>
                      <td><?php echo $AppUI->_('Show'); ?>:</td>
                      <td>
                      <input type="checkbox" name="show_incomplete" id="show_incomplete" onclick="document.task_list_options.submit();"
                       <?php echo $showIncomplete ? 'checked="checked"' : ''; ?> />
                      </td>
                      <td><label for="show_incomplete"><?php echo $AppUI->_('Incomplete Tasks Only'); ?></label></td>
                    </tr>
                </table>
          </td>
        </tr>
    </table>
</form>
<?php }

$fieldList = array();
$fieldNames = array();

$module = new w2p_System_Module();
$fields = $module->loadSettings('tasks', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('task_percent_complete', 'task_priority', 'user_task_priority',
        'task_name', 'user_username', '', 'task_start_date',
        'task_duration', 'task_end_date');
    $fieldNames = array('Work', 'P', 'U', 'Task Name', 'Task Owner',
        'Assigned Users', 'Start Date', 'Duration', 'Finish Date');

    //$module->storeSettings('tasks', 'index_list', $fieldList, $fieldNames);
}
if ($history_active) {
    $fieldList[] = 'last_update';
    $fieldNames[] = 'Last Update';
}
if ($showEditCheckbox) {
    $fieldList[] = '';
    $fieldNames[] = '';
}
?>
<table id="tblProjects" class="tbl list">
    <tr>
        <?php
        echo '<th></th><th></th><th></th>';
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=files&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }

        // Number of columns (used to calculate how many columns to span things through)
        $cols = count($fieldNames) + 3;

        ?>
    </tr>
	<?php
		reset($projects);
		
		if ($w2Pconfig['direct_edit_assignment']) {
			// get Users with all Allocation info (e.g. their freeCapacity)
			// but do it only when direct_edit_assignment is on and only once.
			$tempoTask = new CTask();
			$userAlloc = $tempoTask->getAllocation('user_id', null, true);
		}
		foreach ($projects as $k => $p) {
			$tnums = (isset($p['tasks'])) ? count($p['tasks']) : 0;
			if ($tnums > 0 || $project_id == $p['project_id']) {
				//echo '<pre>'; print_r($p); echo '</pre>';
				if (!$min_view) {
					// not minimal view
					$open_link = w2PtoolTip($m, 'Click to Expand/Collapse the Tasks for this Project.') . '<a href="javascript: void(0);"><img onclick="expand_collapse(\'project_' . $p['project_id'] . '_\', \'tblProjects\',\'collapse\',0,2);" id="project_' . $p['project_id'] . '__collapse" src="' . w2PfindImage('up22.png', $m) . '" class="center" ' . (!$expanded ? 'style="display:none"' : '') . ' alt="" /><img onclick="expand_collapse(\'project_' . $p['project_id'] . '_\', \'tblProjects\',\'expand\',0,2);" id="project_' . $p['project_id'] . '__expand" src="' . w2PfindImage('down22.png', $m) . '" class="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>' . w2PendTip();

					?>
					<tr>
					  <td colspan="<?php echo $cols; ?>">
							<form name="assFrm<?php echo ($p['project_id']) ?>" action="index.php?m=<?php echo ($m); ?>&amp;=<?php echo ($a); ?>" method="post" accept-charset="utf-8">
							<input type="hidden" name="del" value="1" />
							<input type="hidden" name="rm" value="0" />
							<input type="hidden" name="store" value="0" />
							<input type="hidden" name="dosql" value="do_task_assign_aed" />
							<input type="hidden" name="project_id" value="<?php echo ($p['project_id']); ?>" />
							<input type="hidden" name="hassign" />
							<input type="hidden" name="htasks" />
						</td>
					</tr>
					<tr>
					  <td>
					   <?php echo $open_link; ?>
					  </td>
					  <td colspan="<?php echo ($w2Pconfig['direct_edit_assignment']) ? $cols - 3 : $cols; ?>">
						  <table width="100%" border="0">
							  <tr>
									<!-- patch 2.12.04 display company name next to project name -->
									<td nowrap="nowrap" style="border: outset #eeeeee 1px;background-color:#<?php echo $p['project_color_identifier']; ?>">
										<a href="./index.php?m=projects&amp;a=view&amp;project_id=<?php echo $k; ?>">
											<span style="color:<?php echo bestColor($p['project_color_identifier']); ?>;text-decoration:none;">
											<strong><?php echo $p['company_name'] . ' :: ' . $p['project_name']; ?></strong></span>
										</a>
									</td>
									<td width="<?php echo (101 - (int) $p['project_percent_complete']); ?>%">
										<?php echo (int) $p['project_percent_complete']; ?>%
									</td>
							  </tr>
						  </table>
					  </td>
						<?php
							if ($w2Pconfig['direct_edit_assignment']) {
								?>
							  <td colspan="3" align="right" valign="middle">
								  <table width="100%" border="0">
									  <tr>
											<td align="right">
												<select name="add_users" style="width:200px" size="2" multiple="multiple" class="text" ondblclick="javascript:chAssignment(<?php echo ($p['project_id']); ?>, 0, false)">
													<?php
															foreach ($userAlloc as $v => $u) {
																echo '<option value="' . $u['user_id'] . '">' . w2PformSafe($u['userFC']) . "</option>\n";
															}
													?>
												</select>
											</td>
											<td align="center">
												<?php
													echo ('<a href="javascript:chAssignment(' . $p['project_id'] . ', 0, 0);">' . w2PshowImage('add.png', 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks', 'tasks') . "</a>\n");
													echo ('<a href="javascript:chAssignment(' . $p['project_id'] . ', 1, 1);">' . w2PshowImage('remove.png', 16, 16, 'Unassign Users', 'Unassign Users from Task', 'tasks') . "</a>\n");
												?>
												<br />
												<select class="text" name="percentage_assignment" title="<?php echo ($AppUI->_('Assign with Percentage')); ?>" >
													<?php
														for ($i = 0; $i <= 100; $i += 5) {
															echo ("\t" . '<option ' . (($i == 30) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>');
														}
													?>
												</select>
											</td>
									  </tr>
								  </table>
							  </td>
								<?php
							}
						?>
					</tr>
					<?php
				}
		
				if ($task_sort_item1 != '') {
					if ($task_sort_item2 != '' && $task_sort_item1 != $task_sort_item2) {
						$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1, $task_sort_item2, $task_sort_order2, $task_sort_type2);
					} else {
						$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1);
					}
				}
		
				global $tasks_filtered, $children_of;
				//get list of task ids and set-up array of children
				if (isset($p['tasks']) && is_array($p['tasks'])) {
					foreach ($p['tasks'] as $i => $t) {
						$tasks_filtered[] = $t['task_id'];
						$children_of[$t['task_parent']] = (isset($t['task_parent']) && isset($children_of[$t['task_parent']]) && $children_of[$t['task_parent']]) ? $children_of[$t['task_parent']] : array();
						if ($t['task_parent'] != $t['task_id']) {
							array_push($children_of[$t['task_parent']], $t['task_id']);
						}
					}
		
					global $shown_tasks;
					$shown_tasks = array();
					$parent_tasks = array();
					reset($p);
					//1st pass) parent tasks and its children
					foreach ($p['tasks'] as $i => $t1) {
						if (($t1['task_parent'] == $t1['task_id']) && !$task_id) {
							//Here we are NOT on a task view context, like the tasks module list or the project view tasks list.
							
							//check for child
							$no_children = empty($children_of[$t1['task_id']]);
	
							echo showtask_new($t1);
							$shown_tasks[$t1['task_id']] = $t1['task_id'];
                            findchild_new($p['tasks'], $t1['task_id']);
						} elseif ($t1['task_parent'] == $task_id && $task_id) {
							//Here we are on a task view context
		
							//check for child
							$no_children = empty($children_of[$t1['task_id']]);
	
							echo showtask_new($t1);
							$shown_tasks[$t1['task_id']] = $t1['task_id'];
                            findchild_new($p['tasks'], $t1['task_id']);
						}
					}
					reset($p);
					//2nd pass parentless tasks
					foreach ($p['tasks'] as $i => $t1) {
						if (!isset($shown_tasks[$t1['task_id']])) {
							//Here we are on a parentless task context, this can happen because we are:
							//1) displaying filtered tasks that could be showing only child tasks and not its parents due to filtering.
							//2) in a situation where child tasks are active and parent tasks are inactive or vice-versa.
							//
							//The IF condition makes sure:
							//1) The parent task has been displayed and passed through the findchild first, so child tasks are not erroneously displayed as orphan (parentless) 
							//2) Only not displayed yet tasks are shown so we don't show duplicates due to findchild that may cause duplicate showtasks for level 1 (and higher) tasks.
							echo showtask_new($t1, -1);
							$shown_tasks[] = $t1['task_id'];
						}
					}
				}
		
				if ($tnums && $w2Pconfig['enable_gantt_charts'] && !$min_view) {
					?>
					<tr>
                        <td colspan="<?php echo $cols; ?>" align="right">
                            <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Reports'); ?>"
                                   onclick="javascript:window.location='index.php?m=reports&amp;project_id=<?php echo $k; ?>';" />
                            <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Gantt Chart'); ?>"
                                   onclick="javascript:window.location='index.php?m=tasks&amp;a=viewgantt&amp;project_id=<?php echo $k; ?>';" />
                        </td>
					</tr>
					</form>
					<?php
				}
			}
		}
	?>
</table>
<?php
include $AppUI->getTheme()->resolveTemplate('task_key');