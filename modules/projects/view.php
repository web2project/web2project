<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$project_id = (int) w2PgetParam($_GET, 'project_id', 0);

$project = new CProject();

if (!$project->load($project_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $project->canEdit();
$canDelete = $project->canDelete();


$tab = $AppUI->processIntState('ProjVwTab', $_GET, 'tab', 0);

//TODO: is this different from the above checks for some reason?
// Now check if the project is editable/viewable.
$denied = $project->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect(ACCESS_DENIED);
}

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks($project_id) : null;

// create Date objects from the datetime fields
$end_date = intval($project->project_end_date) ? new w2p_Utilities_Date($project->project_end_date) : null;
$actual_end_date = null;
if (isset($criticalTasks)) {
    $actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : null;
}
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Project', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canEdit) {
    $titleBlock->addButton('new link', '?m=links&a=addedit&project_id=' . $project_id);
    $titleBlock->addButton('new event', '?m=events&a=addedit&project_id=' . $project_id);
    $titleBlock->addButton('new file', '?m=files&a=addedit&project_id=' . $project_id);
	$titleBlock->addCrumb('?m=projects&a=addedit&project_id=' . $project_id, 'edit this project');
	if ($canDelete) {
		$titleBlock->addCrumbDelete('delete project', $canDelete);
	}
}
if (canAdd('tasks')) {
    $titleBlock->addButton('new task', '?m=tasks&a=addedit&task_project=' . $project_id);
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $project, 'Project');
echo $view->renderDelete();
?>
<script language="javascript" type="text/javascript">
function expand_multiproject(id, table_name) {
      var trs = document.getElementsByTagName('tr');

      for (var i=0, i_cmp=trs.length;i < i_cmp;i++) {
          var tr_name = trs.item(i).id;

          if (tr_name.indexOf(id) >= 0) {
                 var tr = document.getElementById(tr_name);
                 tr.style.visibility = (tr.style.visibility == '' || tr.style.visibility == 'collapse') ? 'visible' : 'collapse';
                 var img_expand = document.getElementById(id+'_expand');
                 var img_collapse = document.getElementById(id+'_collapse');
                 img_collapse.style.display = (tr.style.visibility == 'visible') ? 'inline' : 'none';
                 img_expand.style.display = (tr.style.visibility == '' || tr.style.visibility == 'collapse') ? 'inline' : 'none';
          }
      }
}
</script>
<?php

$projectPriority = w2PgetSysVal('ProjectPriority');
$projectPriorityColor = w2PgetSysVal('ProjectPriorityColor');
$billingCategory = w2PgetSysVal('BudgetCategory');
$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

include $AppUI->getTheme()->resolveTemplate('projects/view');

$tabBox = new CTabBox('?m=projects&a=view&project_id=' . $project_id, '', $tab);
$query_string = '?m=projects&a=view&project_id=' . $project_id;
// tabbed information boxes
// Note that we now control these based upon module requirements.
$canViewTask = canView('tasks');
$canViewTaskLog = canView('task_log');

//TODO: This whole structure is hard-coded based on the TaskStatus SelectList.
$status = w2PgetSysVal('TaskStatus');
if ($canViewTask && $AppUI->isActiveModule('tasks')) {
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_tasks', 'Tasks');
    unset($status[0]);
    $tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_tasks_inactive', 'Tasks (Inactive)');
    unset($status[-1]);

    foreach ($status as $id => $statusName) {
        $tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', $AppUI->_('Tasks') . ' (' . $AppUI->_($statusName) . ')');
    }

	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/viewgantt', 'Gantt Chart');
	if ($canViewTaskLog) {
		$tabBox->add(W2P_BASE_DIR . '/modules/projects/vw_logs', 'Task Logs');
	}
}

$f = 'all';
$min_view = true;

$tabBox->show();
