<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $a, $addPwOiD, $addPwT, $AppUI, $buffer, $dept_id, $department, $min_view, 
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
	$AppUI->setState('addProjWithTasks', w2PgetParam($_POST, 'add_pwt', 0));
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
projects_list_data($user_id);
$department = $tmpDepartments;

?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<form action="?m=departments&a=view&dept_id=<?php echo $dept_id; ?>&tab=<?php echo $tab; ?>" method="post" name="form_cb" accept-charset="utf-8">
	<input type="hidden" name="show_form" value="1" />
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by'); ?>:&nbsp;</td>
	<td align="center" width="100%" nowrap="nowrap" colspan="6">&nbsp;</td><td align="right" nowrap="nowrap"><input type="checkbox" name="add_pwoid" id="add_pwoid" onclick="document.form_cb.submit()" <?php echo $addPwOiD ? 'checked="checked"' : ''; ?> /><label for="add_pwoid"><?php echo $AppUI->_('Show Projects whose Owner is Member of the Dep.'); ?>?</label></td>
	<td align="right" nowrap="nowrap"><form action="?m=departments&a=view&dept_id=<?php echo $dept_id; ?>&tab=<?php echo $tab; ?>" method="post" name="checkPwT" accept-charset="utf-8"><input type="checkbox" name="add_pwt" id="add_pwt" onclick="document.form_cb.submit()" <?php echo $addPwT ? 'checked="checked"' : ''; ?> /><label for="add_pwt"><?php echo $AppUI->_('Show Projects with assigned Tasks'); ?>?</label></td>
	</form>
	<td align="right" nowrap="nowrap"><form action="?m=departments&a=view&dept_id=<?php echo $dept_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickProject" accept-charset="utf-8"><?php echo arraySelect($projFilter, 'proFilter', 'size=1 class=text onChange="document.pickProject.submit()"', $proFilter, true); ?></form></td>
</tr>
</table>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array('project_color_identifier', 'project_priority',
            'project_name', 'company_name', 'project_start_date', 'project_duration',
            'project_end_date', 'project_actual_end_date', 'task_log_problem',
            'user_username', 'project_task_count', 'project_status');
        $fieldNames = array('Color', 'P', 'Project Name', 'Company', 'Start',
            'Duration', 'End', 'Actual', 'LP', 'Owner', 'Tasks', 'Status');
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
foreach ($projects as $row) {
	// We dont check the percent_completed == 100 because some projects
	// were being categorized as completed because not all the tasks
	// have been created (for new projects)
	if ($proFilter == -1 || $row['project_status'] == $proFilter || ($proFilter == -2 && $row['project_status'] != 3) || ($proFilter == -3 && $row['project_active'] != 0)) {
		$none = false;
		$start_date = intval($row['project_start_date']) ? new w2p_Utilities_Date($row['project_start_date']) : null;
		$end_date = intval($row['project_end_date']) ? new w2p_Utilities_Date($row['project_end_date']) : null;
		$actual_end_date = intval($row['project_actual_end_date']) ? new w2p_Utilities_Date($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr><td width="65" align="right" style="border: outset #eeeeee 1px;background-color:#' . $row['project_color_identifier'] . '"><font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</font></td>';

		$s .= '<td align="center">';
		if ($row['project_priority'] < 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority-' . -$row['project_priority'] . '.gif') . '" width="13" height="16" alt="">';
		} elseif ($row['project_priority'] > 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority+' . $row['project_priority'] . '.gif') . '"  width="13" height="16" alt="">';
		}
		$s .= '</td><td width="40%"><a href="?m=projects&a=view&project_id=' . $row['project_id'] . '" ><span title="' . (nl2br(htmlspecialchars($row['project_description'])) ? htmlspecialchars($row['project_name'], ENT_QUOTES) . '::' . nl2br(htmlspecialchars($row['project_description'])) : '') . '" >' . htmlspecialchars($row['project_name'], ENT_QUOTES) . '</span></a></td>';
		$s .= '<td width="30%"><a href="?m=companies&a=view&company_id=' . $row['project_company'] . '" ><span title="' . (nl2br(htmlspecialchars($row['company_description'])) ? htmlspecialchars($row['company_name'], ENT_QUOTES) . '::' . nl2br(htmlspecialchars($row['company_description'])) : '') . '" >' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</span></a></td>';
		$s .= '<td nowrap="nowrap" align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
		$s .= '<td nowrap="nowrap" align="right">' . ($row['project_duration'] > 0 ? round($row['project_duration'], 0) . $AppUI->_('h') : '-') . '</td>';
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
		$s .= '</td><td align="center" nowrap="nowrap">' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</td>';
		$s .= '<td align="center" nowrap="nowrap">';
		$s .= $row['project_task_count'];
		$s .= '</td><td align="left" nowrap="nowrap">' . $AppUI->_($pstatus[$row['project_status']]) . '</td></tr>';
		echo $s;
	}
}
if ($none) {
	echo '<tr><td colspan="12">' . $AppUI->_('No projects available') . '</td></tr>';
}
?>
<tr>
	<td colspan="12">&nbsp;</td>
</tr>
</table>