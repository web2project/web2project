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

// load the companies class to retrieved denied companies
require_once ($AppUI->getModuleClass('companies'));

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

require_once ($AppUI->getModuleClass('projects'));

// collect the full projects list data via function in projects.class.php
projects_list_data($user_id);
?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="center" width="100%" nowrap="nowrap" colspan="7">&nbsp;</td>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="checkPwT"><td align="right" nowrap="nowrap"><input type="checkbox" name="add_pwt" id="add_pwt" onclick="document.checkPwT.submit()" <?php echo $addPwT ? 'checked="checked"' : ''; ?> /></td><td align="right" nowrap="nowrap"><label for="add_pwt"><?php echo $AppUI->_('Show Projects with assigned Tasks'); ?>?</label><input type="hidden" name="show_form" value="1" /></td></form>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickCompany"><td align="right" nowrap="nowrap"><?php echo $buffer; ?></td></form>
	<form action="?m=admin&a=viewuser&user_id=<?php echo $user_id; ?>&tab=<?php echo $tab; ?>" method="post" name="pickProject"><td align="right" nowrap="nowrap"><?php echo arraySelect($projFilter, 'proFilter', 'size=1 class=text onChange="document.pickProject.submit()"', $proFilter, true); ?></td></form>
</tr>
</table>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_color_identifier" class="hdr"><?php echo $AppUI->_('Color'); ?></a>
    </th>
	</th>
        <th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_priority" class="hdr"><?php echo $AppUI->_('P'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_name" class="hdr"><?php echo $AppUI->_('Project Name'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=company_name" class="hdr"><?php echo $AppUI->_('Company'); ?></a>
	</th>
     <th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_start_date" class="hdr"><?php echo $AppUI->_('Start'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_duration" class="hdr"><?php echo $AppUI->_('Duration'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_end_date" class="hdr"><?php echo $AppUI->_('Due Date'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_actual_end_date" class="hdr"><?php echo $AppUI->_('Actual'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=task_log_problem" class="hdr"><?php echo $AppUI->_('LP'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=total_tasks" class="hdr"><?php echo $AppUI->_('Tasks'); ?></a>
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=my_tasks" class="hdr">(<?php echo $AppUI->_('My'); ?>)</a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=<?php echo $m; ?><?php echo (isset($a) ? '&a=' . $a : ''); ?><?php echo (isset($extraGet) ? $extraGet : ''); ?>&orderby=project_status" class="hdr"><?php echo $AppUI->_('Status'); ?></a>
	</th>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
$none = true;
foreach ($projects as $row) {
	// We dont check the percent_completed == 100 because some projects
	// were being categorized as completed because not all the tasks
	// have been created (for new projects)
	if ($proFilter == -1 || $row['project_status'] == $proFilter || ($proFilter == -2 && $row['project_status'] != 3) || ($proFilter == -3 && $row['project_active'] != 0)) {
		$none = false;
		$start_date = intval(@$row['project_start_date']) ? new CDate($row['project_start_date']) : null;
		$end_date = intval(@$row['project_end_date']) ? new CDate($row['project_end_date']) : null;
		$actual_end_date = intval(@$row['project_actual_end_date']) ? new CDate($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr>';
		$s .= '<td width="65" align="right" style="border: outset #eeeeee 1px;background-color:#' . $row['project_color_identifier'] . '">';
		$s .= $CT . '<font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</font>';
		$s .= $CR . '</td>';

		$s .= $CR . '<td align="center">';
		if ($row['project_priority'] < 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority-' . -$row['project_priority'] . '.gif') . '" width=13 height=16>';
		} elseif ($row['project_priority'] > 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority+' . $row['project_priority'] . '.gif') . '"  width=13 height=16>';
		}
		$s .= $CR . '</td>';

		$s .= $CR . '<td width="40%">';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row['project_id'] . '" ><span title="' . (nl2br(htmlspecialchars($row['project_description'])) ? htmlspecialchars($row['project_name'], ENT_QUOTES) . '::' . nl2br(htmlspecialchars($row['project_description'])) : '') . '" >' . htmlspecialchars($row['project_name'], ENT_QUOTES) . '</span></a>';
		$s .= $CR . '</td>';
		
		$s .= $CR . '<td width="30%">';
		$s .= $CT . '<a href="?m=companies&a=view&company_id=' . $row['project_company'] . '" ><span title="' . (nl2br(htmlspecialchars($row['company_description'])) ? htmlspecialchars($row['company_name'], ENT_QUOTES) . '::' . nl2br(htmlspecialchars($row['company_description'])) : '') . '" >' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</span></a>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap" align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
		$s .= $CR . '<td nowrap="nowrap" align="right">' . ($row['project_duration'] > 0 ? round($row['project_duration'], 0) . $AppUI->_('h') : '-') . '</td>';
		$s .= $CR . '<td nowrap="nowrap" align="center" nowrap="nowrap" style="background-color:' . $priority[$row['project_priority']]['color'] . '">';
		$s .= $CT . ($end_date ? $end_date->format($df) : '-');
		$s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap" align="center">';
		$s .= $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $row['critical_task'] . '">' : '';
		$s .= $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
		$s .= $actual_end_date ? '</a>' : '';
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="center">';
		$s .= $row['task_log_problem'] ? '<a href="?m=tasks&a=index&f=all&project_id=' . $row['project_id'] . '">' : '';
		$s .= $row['task_log_problem'] ? w2PshowImage('icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem') : '-';
		$s .= $CR . $row['task_log_problem'] ? '</a>' : '';
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row['total_tasks'] . ($row['my_tasks'] ? ' (' . $row['my_tasks'] . ')' : '');
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="left" nowrap="nowrap">' . $AppUI->_($pstatus[$row['project_status']]) . '</td>';
		$s .= $CR . '</tr>';
		echo $s;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="12">' . $AppUI->_('No projects available') . '</td></tr>';
}
?>
<tr>
	<td colspan="12">&nbsp;</td>
</tr>
</table>