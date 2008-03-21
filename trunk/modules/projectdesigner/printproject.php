<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $w2Pconfig;
// check permissions for this module
$perms = &$AppUI->acl();
$canView = $perms->checkModule($m, 'view');
$canAddProject = $perms->checkModuleItem('projects', 'view', $project_id);

if (!$canView) {
	$AppUI->redirect('m=public&a=access_denied');
}
$project_id = intval(w2PgetParam($_REQUEST, 'project_id', 0));
$project = new CProject();
$projects = $project->getAllowedRecords($AppUI->user_id, 'projects.project_id,project_name', 'project_name', null, $extra, 'projects');
$q = new DBQuery;
$q->addTable('projects');
$q->addQuery('projects.project_id, company_name');
$q->addJoin('companies', 'co', 'co.company_id = project_company');
$idx_companies = $q->loadHashList();
$q->clear();
foreach ($projects as $prj_id => $prj_name) {
	$projects[$prj_id] = $idx_companies[$prj_id] . ': ' . $prj_name;
}
asort($projects);
$projects = arrayMerge(array('0' => $AppUI->_('(None)', UI_OUTPUT_RAW)), $projects);

$task = new CTask();
$tasks = $task->getAllowedRecords($AppUI->user_id, 'task_id,task_name', 'task_name', null, $extra);
$tasks = arrayMerge(array('0' => $AppUI->_('(None)', UI_OUTPUT_RAW)), $tasks);
// check permissions for this record
$canReadProject = $perms->checkModuleItem('projects', 'view', $project_id);
$canEditProject = $perms->checkModuleItem('projects', 'edit', $project_id);
$canViewTasks = $perms->checkModule('tasks', 'view');
$canAddTasks = $perms->checkModule('tasks', 'add');
$canEditTasks = $perms->checkModule('tasks', 'edit');
$canDeleteTasks = $perms->checkModule('tasks', 'delete');

if (!$canReadProject) {
	$AppUI->redirect('m=public&a=access_denied');
}

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CProject();
// Now check if the project is editable/viewable.
$denied = $obj->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect('m=public&a=access_denied');
}

$canDeleteProject = $obj->canDelete($msg, $project_id);

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $obj->getCriticalTasks($project_id) : null;

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');
$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$working_hours = ($w2Pconfig['daily_working_hours'] ? $w2Pconfig['daily_working_hours'] : 8);

$q = new DBQuery;
//check that project has tasks; otherwise run seperate query
$q->addTable('tasks');
$q->addQuery('COUNT(distinct tasks.task_id) AS total_tasks');
$q->addWhere('task_project = ' . (int)$project_id);
$hasTasks = $q->loadResult();
$q->clear();

// load the record data
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$obj = null;
if ($hasTasks) {
	$q->addTable('projects');
	$q->addQuery('company_name, CONCAT_WS(" ",contact_first_name,contact_last_name) user_name, ' . 'projects.*,' . " SUM(t1.task_duration * t1.task_percent_complete" . " * IF(t1.task_duration_type = 24, {$working_hours}, t1.task_duration_type))" . " / SUM(t1.task_duration * IF(t1.task_duration_type = 24, {$working_hours}, t1.task_duration_type))" . " AS project_percent_complete");
	$q->addJoin('companies', 'com', 'company_id = project_company');
	$q->addJoin('users', 'u', 'user_id = project_owner');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact');
	$q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project');
	$q->addWhere('projects.project_id = ' . (int)$project_id . ' AND t1.task_id = t1.task_parent');
	$q->addGroup('projects.project_id');
	$q->loadObject($obj);
} else {
	$q->addTable('projects');
	$q->addQuery("company_name, CONCAT_WS(' ',contact_first_name,contact_last_name) user_name, " . 'projects.*, ' . "(0.0) AS project_percent_complete");
	$q->addJoin('companies', 'com', 'company_id = project_company');
	$q->addJoin('users', 'u', 'user_id = project_owner');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact');
	$q->addWhere('projects.project_id = ' . (int)$project_id);
	$q->addGroup('projects.project_id');
	$q->loadObject($obj);
}
$q->clear();

if (!$obj) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg("invalidID", UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// worked hours
// now milestones are summed up, too, for consistence with the tasks duration sum
// the sums have to be rounded to prevent the sum form having many (unwanted) decimals because of the mysql floating point issue
// more info on http://www.mysql.com/doc/en/Problems_with_float.html
if ($hasTasks) {
	$q->addTable('task_log');
	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_log_hours),2)');
	$q->addWhere('task_log_task = task_id AND task_project = ' . (int)$project_id);
	$worked_hours = $q->loadResult();
	$q->clear();
	$worked_hours = rtrim($worked_hours, '.');

	// total hours
	// same milestone comment as above, also applies to dynamic tasks
	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_duration),2)');
	$q->addWhere('task_project = ' . (int)$project_id . ' AND task_duration_type = 24 AND task_dynamic != 1');
	$days = $q->loadResult();
	$q->clear();

	$q->addTable('tasks');
	$q->addQuery('ROUND(SUM(task_duration),2)');
	$q->addWhere('task_project = ' . (int)$project_id . ' AND task_duration_type = 1 AND task_dynamic != 1');
	$hours = $q->loadResult();
	$q->clear();
	$total_hours = $days * $w2Pconfig['daily_working_hours'] + $hours;

	$total_project_hours = 0;

	$q->addTable('tasks', 't');
	$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
	$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
	$q->addWhere('t.task_project = ' . (int)$project_id . ' AND t.task_duration_type = 24 AND t.task_dynamic != 1');
	$total_project_days_sql = $q->prepare();

	$q2 = new DBQuery;
	$q2->addTable('tasks', 't');
	$q2->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
	$q2->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
	$q2->addWhere('t.task_project = ' . (int)$project_id . ' AND t.task_duration_type = 1 AND t.task_dynamic != 1');

	$total_project_hours = $q->loadResult() * $w2Pconfig['daily_working_hours'] + $q2->loadResult();
	$q->clear();
	$q2->clear();
	//due to the round above, we don't want to print decimals unless they really exist
	//$total_project_hours = rtrim($total_project_hours, "0");
} else { //no tasks in project so "fake" project data
	$worked_hours = $total_hours = $total_project_hours = 0.00;
}


?>

<?php
$priorities = w2Pgetsysval('TaskPriority');
$types = w2Pgetsysval('TaskType');
include_once ($AppUI->getModuleClass('tasks'));
global $task_access;
$extra = array(0 => '(none)', 1 => 'Milestone', 2 => 'Dynamic Task', 3 => 'Inactive Task');
?>

<style type="text/css">
/* Standard table 'spreadsheet' style */
TABLE.prjprint {
	background: #ffffff;
}

TABLE.prjprint TH {
	background-color: #ffffff;
	color: black;
	list-style-type: disc;
	list-style-position: inside;
	border:solid 1px;
	font-weight: normal;
	font-size:15px;
}

TABLE.prjprint TD {
	background-color: #ffffff;
	font-size:14px;
}

TABLE.prjprint TR {
	padding:5px;
}
	
</style>
<table width="100%" class="prjprint">
<form name="frmDelete" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
</form>

<tr>
	<td style="border: outset #d1d1cd 1px;" colspan="3">  
		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="prjprint">	
            <tr>
            	<td width="22">
            	&nbsp;
            	</td>
            	<td align="center"  colspan="2">
            	<?php
echo '<strong> Project Report <strong>';
?>
            	</td>
        <!--	    <td width="22" align="right">
				<a href="javascript: void(0);" onclick="var img=document.getElementById('imghd'); img.style.display='none'; window.print(); window.close();">
      			<img id="imghd" src="./modules/projectdesigner/images/printer.png" border="0" width="22" heigth="22" alt="print project" title="print project"/>
      			</a>
      			</td>-->  
      	</tr>
      	</table>
	</td>
</tr>
	<?php
if ($canReadProject) {
	require (w2PgetConfig('root_dir') . '/modules/projectdesigner/vw_projecttask.php');
} else {
	echo $AppUI->_('You do not have permission to view tasks');
}
?>
</table>