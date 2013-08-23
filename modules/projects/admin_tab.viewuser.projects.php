<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $a, $addPwT, $AppUI, $buffer, $company_id, $department, $min_view, $m, $priority, $projects, $tab, $user_id, $orderdir, $orderby;

$df = $AppUI->getPref('SHDATEFORMAT');

$pstatus = w2PgetSysVal('ProjectStatus');

if (isset($_POST['proFilter'])) {
	$AppUI->setState('UsrProjectIdxFilter', $_POST['proFilter']);
}
$proFilter = $AppUI->getState('UsrProjectIdxFilter') !== null ? $AppUI->getState('UsrProjectIdxFilter') : '-3';

$projFilter = arrayMerge(array('-1' => 'All Projects'), $pstatus);
$projFilter = arrayMerge(array('-2' => 'All w/o in progress'), $projFilter);
$projFilter = arrayMerge(array('-3' => 'All w/o archived'), $projFilter);
natsort($projFilter);

// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('UsrProjIdxTab', w2PgetParam($_GET, 'tab', null));
}

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('UsrProjIdxOrderDir') ? ($AppUI->getState('UsrProjIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('UsrProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('UsrProjIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('UsrProjIdxOrderBy') ? $AppUI->getState('UsrProjIdxOrderBy') : 'project_end_date';
$orderdir = $AppUI->getState('UsrProjIdxOrderDir') ? $AppUI->getState('UsrProjIdxOrderDir') : 'asc';

$extraGet = '&user_id=' . $user_id;

// collect the full projects list data via function in projects.class.php
$project = new CProject();
$projects = projects_list_data($user_id);

$module = new w2p_Core_Module();
$fields = $module->loadSettings('projects', 'admin_view');

if (0 == count($fields)) {
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'company_name', 'project_start_date', 'project_duration',
        'project_end_date', 'project_actual_end_date', 'task_log_problem',
        'user_username', 'project_task_count', 'project_status');
    $fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
        'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');

    $fields = array_combine($fieldList, $fieldNames);
}
?>

<table class="tbl list">
<tr>
	<td align="center" width="100%" nowrap="nowrap" colspan="7">&nbsp;</td>
	<td align="right" nowrap="nowrap"><form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="checkPwT" accept-charset="utf-8"><input type="checkbox" name="add_pwt" id="add_pwt" onclick="document.checkPwT.submit()" <?php echo $addPwT ? 'checked="checked"' : ''; ?> /></td><td align="right" nowrap="nowrap"><label for="add_pwt"><?php echo $AppUI->_('Show Projects with assigned Tasks'); ?>?</label><input type="hidden" name="show_form" value="1" /></form></td>
	<td align="right" nowrap="nowrap"><form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickCompany" accept-charset="utf-8"><?php echo $buffer; ?></form></td>
	<td align="right" nowrap="nowrap"><form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickProject" accept-charset="utf-8"><?php echo arraySelect($projFilter, 'proFilter', 'size=1 class=text onChange="document.pickProject.submit()"', $proFilter, true); ?></form></td>
</tr>
</table>
<?php

$customLookups = array('project_status' => $pstatus);

$none = true;
$listHelper = new w2p_Output_ListTable($AppUI);

echo $listHelper->startTable();
echo $listHelper->buildHeader($fields, true, 'admin&a=viewuser&user_id=' . $user_id);

foreach ($projects as $row) {
    $listHelper->stageRowData($row);
    // We dont check the percent_completed == 100 because some projects
	// were being categorized as completed because not all the tasks
	// have been created (for new projects)
	if ($proFilter == -1 || $row['project_status'] == $proFilter || ($proFilter == -2 && $row['project_status'] != 3) || ($proFilter == -3 && $row['project_active'] != 0)) {
		$none = false;

		$end_date = intval($row['project_end_date']) ? new w2p_Utilities_Date($row['project_end_date']) : null;
		$actual_end_date = intval($row['project_actual_end_date']) ? new w2p_Utilities_Date($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr>';
        $s .= $listHelper->createCell('project_color_identifier', $row['project_color_identifier']);
        $s .= $listHelper->createCell('project_priority', $row['project_priority']);
        $s .= $listHelper->createCell('project_name', $row['project_name']);
        $s .= $listHelper->createCell('project_company', $row['project_company']);
        $s .= $listHelper->createCell('project_start_date', $row['project_start_date']);
        $s .= $listHelper->createCell('project_scheduled_hours', $row['project_scheduled_hours']);
        $s .= $listHelper->createCell('project_end_date', $row['project_end_date']);
        $s .= $listHelper->createCell('project_end_actual', $row['project_actual_end_date']);
        $s .= $listHelper->createCell('task_log_problem', $row['task_log_problem']);
        $s .= $listHelper->createCell('project_owner', $row['project_owner']);
        $s .= $listHelper->createCell('project_task_count', $row['project_task_count']);
        $s .= $listHelper->createCell('project_status', $row['project_status'], $customLookups);
        $s .= '</tr>';
		echo $s;
	}
}
if ($none) {
    echo $listHelper->buildEmptyRow();
}
echo $listHelper->endTable();
