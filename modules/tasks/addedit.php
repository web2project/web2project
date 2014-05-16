<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'task_id', 0);



$object = new CTask();
$object->setId($object_id);

$obj = $object;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
	$AppUI->setMsg('Task');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

$percent = array(0 => '0', 5 => '5', 10 => '10', 15 => '15', 20 => '20', 25 => '25', 30 => '30', 35 => '35', 40 => '40', 45 => '45', 50 => '50', 55 => '55', 60 => '60', 65 => '65', 70 => '70', 75 => '75', 80 => '80', 85 => '85', 90 => '90', 95 => '95', 100 => '100');
$status = w2PgetSysVal('TaskStatus');
$priority = w2PgetSysVal('TaskPriority');

$task_parent = (int) w2PgetParam($_GET, 'task_parent', $object->task_parent);

// check for a valid project parent
$task_project = (int) $object->task_project;
if (!$task_project) {
	$task_project = (int) w2PgetParam($_REQUEST, 'task_project', 0);
    if (!$task_project) {
		$AppUI->setMsg('badTaskProject', UI_MSG_ERROR);
        $AppUI->redirect('m=' . $m);
	}
}

// check permissions
$perms = &$AppUI->acl();
if (!$object_id) {
	// do we have access on this project?
	$canEdit = $perms->checkModuleItem('projects', 'view', $task_project);
	// And do we have add permission to tasks?
	if ($canEdit) {
		$canEdit = $canAuthor;
	}
}

if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}
if (isset($object->task_represents_project) && $object->task_represents_project) {
    $AppUI->setMsg('The selected task represents a subproject. Please view/edit this project instead.', UI_MSG_ERROR);
    $AppUI->redirect('m=projects&a=view&project_id='.$object->task_represents_project);
}

//check permissions for the associated project
$canReadProject = $perms->checkModuleItem('projects', 'view', $object->task_project);

$durnTypes = w2PgetSysVal('TaskDurationType');

// check the document access (public, participant, private)
if (!$object->canAccess($AppUI->user_id)) {
	$AppUI->redirect(ACCESS_DENIED);
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



$projTasks = array();

$parents = array();
$projTasksWithEndDates = array($object->task_id => $AppUI->_('None')); //arrays contains task end date info for setting new task start date as maximum end date of dependenced tasks
$all_tasks = array();

$subtasks = $object->getNonRootTasks($task_project);
foreach ($subtasks as $sub_task) {
    // Build parent/child task list
    $parents[$sub_task['task_parent']][] = $sub_task['task_id'];
    $all_tasks[$sub_task['task_id']] = $sub_task;
    build_date_list($projTasksWithEndDates, $sub_task);
}

$task_parent_options = '';

$root_tasks = $object->getRootTasks((int)$task_project);
foreach ($root_tasks as $root_task) {
    build_date_list($projTasksWithEndDates, $root_task);
	if ($root_task['task_id'] != $object_id) {
        $task_parent_options .= buildTaskTree($root_task, 0, array(), $all_tasks, $parents, $task_parent, $object_id);
	}
}

// setup the title block
$ttl = $object_id > 0 ? 'Edit Task' : 'Add Task';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addViewLink('project', $task_project);
$titleBlock->addViewLink('task', $object_id);
$titleBlock->show();

// Get contacts list
$selected_contacts = array();

if ($object_id) {
	$myContacts = $object->getContacts(null, $object_id);
	$selected_contacts = array_keys($myContacts);
}
if ($object_id == 0 && (isset($contact_id) && $contact_id > 0)) {
	$selected_contacts[] = '' . $contact_id;
}

$department_selection_list = array();
$department = new CDepartment();
$deptList = $department->departments($project->project_company);
foreach($deptList as $dept) {
  $department_selection_list[$dept['dept_id']] = $dept['dept_name'];
}
$department_selection_list = arrayMerge(array('0' => ''), $department_selection_list);

//Dynamic tasks are by default now off because of dangerous behavior if incorrectly used
if (is_null($object->task_dynamic)) {
	$object->task_dynamic = 0;
}

$can_edit_time_information = $object->canUserEditTimeInformation($project->project_owner, $AppUI->user_id);
//get list of projects, for task move drop down list.
$tmpprojects = $project->getAllowedProjects($AppUI->user_id);
$projects = array();
$projects[0] = $AppUI->_('Do not move');
foreach($tmpprojects as $proj) {
    $projects[$proj['project_id']] = $proj['project_name'];
}
?>
<script language="javascript" type="text/javascript">
var task_id = '<?php echo $object->task_id; ?>';

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
var cal_day_start = <?php echo (int) w2PgetConfig('cal_day_start'); ?>;
var cal_day_end = <?php echo (int) w2PgetConfig('cal_day_end'); ?>;
var daily_working_hours = <?php echo (int) w2PgetConfig('daily_working_hours'); ?>;

function popContacts() {
    var selected_contacts_id = document.getElementById('task_contacts').value;
    var project_company = <?php echo $project->project_company; ?>;
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&selected_contacts_id='+selected_contacts_id+'&company_id='+project_company, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('tasks/addedit');

$tab = $AppUI->processIntState('TaskAeTabIdx', $_GET, 'tab', 0);

$tabBox = new CTabBox('?m=tasks&a=addedit&task_id=' . $object_id, '', $tab, '');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_desc', 'Details');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_dates', 'Dates');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_depend', 'Dependencies');
$tabBox->add(W2P_BASE_DIR . '/modules/tasks/ae_resource', 'Human Resources');
$tabBox->show('', true);
