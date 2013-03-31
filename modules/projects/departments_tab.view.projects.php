<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $a, $addPwOiD, $buffer, $dept_id, $department, $min_view,
	$m, $priority, $projects, $tab, $user_id, $orderdir, $orderby;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');

$pstatus = w2PgetSysVal('ProjectStatus');

if (isset($_POST['proFilter'])) {
	$AppUI->setState('DeptProjectIdxFilter', $_POST['proFilter']);
}
$proFilter = $AppUI->getState('DeptProjectIdxFilter') !== null ? $AppUI->getState('DeptProjectIdxFilter') : '-1';

$projFilter = arrayMerge(array('-1' => 'All Projects'), $pstatus);
$projFilter = arrayMerge(array('-2' => 'All w/o in progress'), $projFilter);
$projFilter = arrayMerge(array('-3' => 'All w/o archived'), $projFilter);
natsort($projFilter);

// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('DeptProjIdxTab', w2PgetParam($_GET, 'tab', null));
}

if (isset($_GET['orderby'])) {
	$orderdir = $AppUI->getState('DeptProjIdxOrderDir') ? ($AppUI->getState('DeptProjIdxOrderDir') == 'asc' ? 'desc' : 'asc') : 'desc';
	$AppUI->setState('DeptProjIdxOrderBy', w2PgetParam($_GET, 'orderby', null));
	$AppUI->setState('DeptProjIdxOrderDir', $orderdir);
}
$orderby = $AppUI->getState('DeptProjIdxOrderBy') ? $AppUI->getState('DeptProjIdxOrderBy') : 'project_end_date';
$orderdir = $AppUI->getState('DeptProjIdxOrderDir') ? $AppUI->getState('DeptProjIdxOrderDir') : 'asc';

if (isset($_POST['show_form'])) {
	$AppUI->setState('addProjWithOwnerInDep', w2PgetParam($_POST, 'add_pwoid', 0));
}
$addPwT = $AppUI->getState('addProjWithTasks', 0);
$addPwOiD = $AppUI->getState('addProjWithOwnerInDep', 0);

$extraGet = '&user_id=' . $user_id;

// collect the full projects list data via function in projects.class.php
/*
 *  TODO:  This is a *nasty* *nasty* kludge that should be cleaned up.
 * Unfortunately due to the global variables from dotProject, we're stuck with
 * this mess for now.
 * 
 * May God have mercy on our souls for the atrocity we're about to commit.
 */ 
$tmpDepartments = $department;
$department = $dept_id;
$project = new CProject();
$projects = projects_list_data($user_id);
$department = $tmpDepartments;

$fieldList = array('project_color_identifier', 'project_priority',
    'project_name', 'company_name', 'project_start_date', 'project_duration',
    'project_end_date', 'project_actual_end_date', 'task_log_problem',
    'user_username', 'project_task_count', 'project_status');
$fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
    'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');
?>

<table class="tbl list">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by'); ?>:&nbsp;</td>
	<td align="center" width="100%" nowrap="nowrap" colspan="6">&nbsp;</td>
    <td align="right" nowrap="nowrap">
        <form action="?m=departments&a=view&dept_id=<?php echo $dept_id; ?>&tab=<?php echo $tab; ?>" method="post" name="form_cb" accept-charset="utf-8">
            <input type="hidden" name="show_form" value="1" />
            <input type="checkbox" name="add_pwoid" id="add_pwoid" onclick="document.form_cb.submit()" <?php echo $addPwOiD ? 'checked="checked"' : ''; ?> /><label for="add_pwoid"><?php echo $AppUI->_('Show Projects whose Owner is Member of the Dep.'); ?>?</label>
        </form>
    </td>
	<td align="right" nowrap="nowrap">
        <form action="?m=departments&a=view&dept_id=<?php echo $dept_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickProject" accept-charset="utf-8">
            <?php echo arraySelect($projFilter, 'proFilter', 'size=1 class=text onChange="document.pickProject.submit()"', $proFilter, true); ?>
        </form>
    </td>
</tr>
</table>
<table class="tbl list">
    <tr>
        <?php
        $baseUrl = '?m='.$m.(isset($a) ? '&a=' . $a : '').(isset($extraGet) ? $extraGet : '');
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
                <a href="<?php echo $baseUrl; ?>&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
                </a>
            </th><?php
        }
        ?>
    </tr>

<?php
$none = true;
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$customLookups = array('project_status' => $pstatus);

if (count($projects)) {
    foreach ($projects as $row) {
        $htmlHelper->stageRowData($row);

        // We dont check the percent_completed == 100 because some projects
        // were being categorized as completed because not all the tasks
        // have been created (for new projects)
        if ($proFilter == -1 || $row['project_status'] == $proFilter || ($proFilter == -2 && $row['project_status'] != 3) || ($proFilter == -3 && $row['project_active'] != 0)) {
            $project->project_id = $row['project_id'];
            $none = false;

            $end_date = intval($row['project_end_date']) ? new w2p_Utilities_Date($row['project_end_date']) : null;
            $actual_end_date = intval($row['project_actual_end_date']) ? new w2p_Utilities_Date($row['project_actual_end_date']) : null;
            $style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

            $s = '<tr><td class="data" width="65" style="border: outset #eeeeee 1px;background-color:#' . $row['project_color_identifier'] . ';color:' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</td>';
            $s .= $htmlHelper->createCell('project_priority', $row['project_priority']);
            $s .= $htmlHelper->createCell('project_name', $row['project_name']);
            $s .= $htmlHelper->createCell('project_company', $row['project_company']);
            $s .= $htmlHelper->createCell('project_start_date', $row['project_start_date']);
            $s .= $htmlHelper->createCell('project_scheduled_hours', $row['project_scheduled_hours']);
            $s .= '<td nowrap="nowrap" align="center" nowrap="nowrap" style="background-color:' . $priority[$row['project_priority']]['color'] . '">';
            $s .= ($end_date ? $end_date->format($df) : '-');
            $s .= '</td><td nowrap="nowrap" align="center">';
            $s .= $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $row['critical_task'] . '">' : '';
            $s .= $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
            $s .= $actual_end_date ? '</a>' : '';
            $s .= '</td><td align="center">';
            $s .= $row['task_log_problem'] ? '<a href="?m=tasks&a=index&f=all&project_id=' . $row['project_id'] . '">' : '';
            $s .= $row['task_log_problem'] ? w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem') : '-';
            $s .= $row['task_log_problem'] ? '</a>' : '';
            $s .= '</td>';
            $s .= $htmlHelper->createCell('project_owner', $row['project_owner']);
            $s .= $htmlHelper->createCell('project_task_count', $row['project_task_count']);
            $s .= $htmlHelper->createCell('project_status', $row['project_status'], $customLookups);
            $s .= '</tr>';
            echo $s;
        }
    }
} else {
    echo '<tr><td colspan="'.count($fieldNames).'">' . $AppUI->_('No data available') . '</td></tr>';
}
?>
</table>