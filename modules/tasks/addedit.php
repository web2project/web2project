<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$status = w2PgetSysVal('TaskStatus');
$priority = w2PgetSysVal('TaskPriority');
// user based access
$task_access = array(CTask::ACCESS_PUBLIC => 'Public', CTask::ACCESS_PROTECTED => 'Protected', CTask::ACCESS_PARTICIPANT => 'Participant', CTask::ACCESS_PRIVATE => 'Private');

/**
 * Tasks :: Add/Edit Form
 *
 */

$task_id = (int) w2PgetParam($_GET, 'task_id', 0);
$perms = &$AppUI->acl();

// load the record data
$task = new CTask();
$obj = $AppUI->restoreObject();
if ($obj) {
  $task = $obj;
  $task_id = $task->task_id;
} else {
  $task->loadFull($AppUI, $task_id);
}
if (!$task && $task_id > 0) {
	$AppUI->setMsg('Task');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$task_parent = (int) w2PgetParam($_GET, 'task_parent', $task->task_parent);

// check for a valid project parent
$task_project = (int) $task->task_project;
if (!$task_project) {
	$task_project = (int) w2PgetParam($_REQUEST, 'task_project', 0);
    if (!$task_project) {
		$AppUI->setMsg('badTaskProject', UI_MSG_ERROR);
		$AppUI->redirect();
	}
}

// check permissions
if ($task_id) {
	// we are editing an existing task
	$canEdit = $perms->checkModuleItem('tasks', 'edit', $task_id);
} else {
	// do we have access on this project?
	$canEdit = $perms->checkModuleItem('projects', 'view', $task_project);
	// And do we have add permission to tasks?
	if ($canEdit) {
		$canEdit = canAdd('tasks');
	}
}

if (!$canEdit) {
	$AppUI->redirect('m=public&a=access_denied&err=noedit');
}
if ($task->task_represents_project) {
    $AppUI->setMsg('The selected task represents a subproject. Please view/edit this project instead.', UI_MSG_ERROR);
    $AppUI->redirect('m=projects&a=view&project_id='.$task->task_represents_project);
}

//check permissions for the associated project
$canReadProject = $perms->checkModuleItem('projects', 'view', $task->task_project);

$durnTypes = w2PgetSysVal('TaskDurationType');

// check the document access (public, participant, private)
if (!$task->canAccess($AppUI->user_id)) {
	$AppUI->redirect('m=public&a=access_denied&err=noaccess');
}

// pull the related project
$project = new CProject();
$project->load($task_project);

//Pull all users
// TODO: There's an issue that can arise if a user is assigned full access to 
//   a company which is not their own.  They will be allowed to create 
//   projects but not create tasks since the task_owner dropdown does not get 
//   populated by the "getPermittedUsers" function.
$users = $perms->getPermittedUsers('tasks');

function getSpaces($amount) {
	if ($amount == 0) {
		return '';
	}
	return str_repeat('&nbsp;', $amount);
}

function constructTaskTree($task_data, $depth = 0) {
	global $projTasks, $all_tasks, $parents, $task_parent_options, $task_parent, $task_id;

	$projTasks[$task_data['task_id']] = $task_data['task_name'];

	$selected = $task_data['task_id'] == $task_parent ? 'selected="selected"' : '';
	$task_data['task_name'] = mb_strlen($task_data[1]) > 45 ? mb_substr($task_data['task_name'], 0, 45) . '...' : $task_data['task_name'];

	$task_parent_options .= '<option value="' . $task_data['task_id'] . '" ' . $selected . '>' . getSpaces($depth * 3) . w2PFormSafe($task_data['task_name']) . '</option>';

	if (isset($parents[$task_data['task_id']])) {
		foreach ($parents[$task_data['task_id']] as $child_task) {
			if ($child_task != $task_id) {
				constructTaskTree($all_tasks[$child_task], ($depth + 1));
			}
		}
	}
}

function build_date_list(&$date_array, $row) {
	global $tracked_dynamics, $project;
	// if this task_dynamic is not tracked, set end date to proj start date
	if (!in_array($row['task_dynamic'], $tracked_dynamics)) {
		$date = new w2p_Utilities_Date($project->project_start_date);
	} elseif ($row['task_milestone'] == 0) {
		$date = new w2p_Utilities_Date($row['task_end_date']);
	} else {
		$date = new w2p_Utilities_Date($row['task_start_date']);
	}
	$sdate = $date->format('%d/%m/%Y');
	$shour = $date->format('%H');
	$smin = $date->format('%M');

	$date_array[$row['task_id']] = array($row['task_name'], $sdate, $shour, $smin);
}

// let's get root tasks
$q = new w2p_Database_Query;
$q->addTable('tasks');
$q->addQuery('task_id, task_name, task_end_date, task_start_date, task_milestone, task_parent, task_dynamic');
$q->addWhere('task_project = ' . (int)$task_project);
$q->addWhere('task_id = task_parent');
$q->addOrder('task_start_date');
$root_tasks = $q->loadHashList('task_id');
$q->clear();

$projTasks = array();
$task_parent_options = '';

// Now lets get non-root tasks, grouped by the task parent
$q = new w2p_Database_Query;
$q->addTable('tasks');
$q->addQuery('task_id, task_name, task_end_date, task_start_date, task_milestone, task_parent, task_dynamic');
$q->addWhere('task_project = ' . (int)$task_project);
$q->addWhere('task_id <> task_parent');
$q->addOrder('task_start_date');

$parents = array();
$projTasksWithEndDates = array($task->task_id => $AppUI->_('None')); //arrays contains task end date info for setting new task start date as maximum end date of dependenced tasks
$all_tasks = array();
$sub_tasks = $q->exec();
if ($sub_tasks) {
	while ($sub_task = $q->fetchRow()) {
		// Build parent/child task list
		$parents[$sub_task['task_parent']][] = $sub_task['task_id'];
		$all_tasks[$sub_task['task_id']] = $sub_task;
		build_date_list($projTasksWithEndDates, $sub_task);
	}
}
$q->clear();

// let's iterate root tasks
foreach ($root_tasks as $root_task) {
	build_date_list($projTasksWithEndDates, $root_task);
	if ($root_task['task_id'] != $task_id) {
		constructTaskTree($root_task);
	}
}

// setup the title block
$ttl = $task_id > 0 ? 'Edit Task' : 'Add Task';
$titleBlock = new CTitleBlock($ttl, 'applet-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=tasks', 'tasks list');
if ($canReadProject) {
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $task_project, 'view this project');
}
if ($task_id > 0)
	$titleBlock->addCrumb('?m=tasks&a=view&task_id=' . $task->task_id, 'view this task');
$titleBlock->show();

$department_selection_list = array();
$deptList = CDepartment::getDepartmentList($AppUI, $project->project_company, null);
foreach($deptList as $dept) {
  $department_selection_list[$dept['dept_id']] = $dept['dept_name'];
}
$department_selection_list = arrayMerge(array('0' => ''), $department_selection_list);

//Dynamic tasks are by default now off because of dangerous behavior if incorrectly used
if (is_null($task->task_dynamic)) {
	$task->task_dynamic = 0;
}

$can_edit_time_information = $task->canUserEditTimeInformation();
//get list of projects, for task move drop down list.
$pq = new w2p_Database_Query;
$pq->addQuery('pr.project_id, project_name');
$pq->addTable('projects', 'pr');
$pq->addWhere('( project_active = 1 or pr.project_id = ' . (int)$task_project . ')');
$pq->addOrder('project_name');
$project->setAllowedSQL($AppUI->user_id, $pq, null, 'pr');
$projects = $pq->loadHashList();
?>
<script language="javascript" type="text/javascript">
var selected_contacts_id = '<?php echo $task->task_contacts; ?>';
var task_id = '<?php echo $task->task_id; ?>';

var check_task_dates = <?php
if (isset($w2Pconfig['check_task_dates']) && $w2Pconfig['check_task_dates'])
	echo 'true';
else
	echo 'false';
?>;
var can_edit_time_information = <?php echo $can_edit_time_information ? 'true' : 'false'; ?>;

var task_name_msg = '<?php echo $AppUI->_('taskName'); ?>';
var task_start_msg = '<?php echo $AppUI->_('taskValidStartDate'); ?>';
var task_end_msg = '<?php echo $AppUI->_('taskValidEndDate'); ?>';

var workHours = <?php echo w2PgetConfig('daily_working_hours'); ?>;
//working days array from config.php
var working_days = new Array(<?php echo w2PgetConfig('cal_working_days'); ?>);
var cal_day_start = <?php echo intval(w2PgetConfig('cal_day_start')); ?>;
var cal_day_end = <?php echo intval(w2PgetConfig('cal_day_end')); ?>;
var daily_working_hours = <?php echo intval(w2PgetConfig('daily_working_hours')); ?>;
</script>

<form name="editFrm" action="?m=tasks&project_id=<?php echo $task_project; ?>" method="post" onSubmit="return submitIt(document.editFrm);" accept-charset="utf-8">
	<input name="dosql" type="hidden" value="do_task_aed" />
	<input name="task_id" type="hidden" value="<?php echo $task_id; ?>" />
	<input name="task_project" type="hidden" value="<?php echo $task_project; ?>" />
	<input name="old_task_parent" type="hidden" value="<?php echo $task->task_parent; ?>" />
	<input name='task_contacts' id='task_contacts' type='hidden' value="<?php echo $task->task_contacts; ?>" />
<table border="1" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td colspan="2" style="border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier; ?>" >
		<font color="<?php echo bestColor($project->project_color_identifier); ?>">
			<strong><?php echo $AppUI->_('Project'); ?>: <?php echo $project->project_name; ?></strong>
		</font>
	</td>
</tr>

<tr valign="top">
	<td>
		<?php echo $AppUI->_('Task Name'); ?> *
		<br /><input type="text" class="text" name="task_name" value="<?php echo htmlspecialchars($task->task_name, ENT_QUOTES); ?>" size="40" maxlength="255" />
	</td>
	<td>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?></td>
			<td>
				<?php echo arraySelect($status, 'task_status', 'size="1" class="text"', ($task->task_status ? $task->task_status : 0) , true); ?>
			</td>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?> *</td>
			<td nowrap="nowrap">
				<?php echo arraySelect($priority, 'task_priority', 'size="1" class="text"', ($task->task_priority ? $task->task_priority : 0) , true); ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?></td>
			<td>
				<?php echo arraySelect($percent, 'task_percent_complete', 'size="1" class="text"', $task->task_percent_complete) . '%'; ?>
			</td>

			<td align="right" nowrap="nowrap"><label for="task_milestone"><?php echo $AppUI->_('Milestone'); ?>?</label></td>
			<td>
				<input type="checkbox" value="1" name="task_milestone" id="task_milestone" <?php if ($task->task_milestone) { ?>checked="checked"<?php } ?> onClick="toggleMilestone()" />
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="3" width="100%">
		<tr>
			<td height="40" width="35%">
				* <?php echo $AppUI->_('requiredField'); ?>
			</td>
			<td height="40" width="30%">&nbsp;</td>
			<td  height="40" width="35%" align="right">
				<table>
				<tr>
					<td>
						<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="if(confirm('<?php echo $AppUI->_('taskCancel', UI_OUTPUT_JS); ?>')){location.href = '?<?php echo $AppUI->getPlace(); ?>';}" />
					</td>
					<td>
						<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save'); ?>" onclick="submitIt(document.editFrm);" />
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>	
</form>
</table>

<?php
$tab = $AppUI->processIntState('TaskAeTabIdx', $_GET, 'tab', 0);

$tabBox = new CTabBox('?m=tasks&a=addedit&task_id=' . $task_id, '', $tab, '');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_desc', 'Details');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_dates', 'Dates');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_depend', 'Dependencies');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_resource', 'Human Resources');
$tabBox->show('', true);