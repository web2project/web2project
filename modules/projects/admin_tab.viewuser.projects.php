<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $a, $addPwT, $AppUI, $buffer, $company_id, $department, $min_view, $m, $priority, $projects, $tab, $user_id, $orderdir, $orderby;

$perms = &$AppUI->acl();
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
projects_list_data($user_id);
?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="center" width="100%" nowrap="nowrap" colspan="7">&nbsp;</td>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="checkPwT" accept-charset="utf-8"><td align="right" nowrap="nowrap"><input type="checkbox" name="add_pwt" id="add_pwt" onclick="document.checkPwT.submit()" <?php echo $addPwT ? 'checked="checked"' : ''; ?> /></td><td align="right" nowrap="nowrap"><label for="add_pwt"><?php echo $AppUI->_('Show Projects with assigned Tasks'); ?>?</label><input type="hidden" name="show_form" value="1" /></td></form>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickCompany" accept-charset="utf-8"><td align="right" nowrap="nowrap"><?php echo $buffer; ?></td></form>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickProject" accept-charset="utf-8"><td align="right" nowrap="nowrap"><?php echo arraySelect($projFilter, 'proFilter', 'size=1 class=text onChange="document.pickProject.submit()"', $proFilter, true); ?></td></form>
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
		$start_date = intval($row['project_start_date']) ? new CDate($row['project_start_date']) : null;
		$end_date = intval($row['project_end_date']) ? new CDate($row['project_end_date']) : null;
		$actual_end_date = intval($row['project_actual_end_date']) ? new CDate($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr><td width="65" align="right" style="border: outset #eeeeee 1px;background-color:#' . $row['project_color_identifier'] . '">';
		$s .= '<font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</font></td><td align="center">';
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
		$s .= '</td><td align="center" nowrap="nowrap">' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</td><td align="center" nowrap="nowrap">';
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