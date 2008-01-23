<?php // create Date objects from the datetime fields
global $AppUI, $dPconfig, $projects, $company_id, $pstatus, $project_types, $project_status_filter, $currentTabId, $currentTabName, $projectDesigner;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
//$df = '%m/%d/%y';
// Let's check if the user submited the change status form

$projectTypes = w2PgetSysVal('ProjectStatus');

$show_all_projects = false;
$currentTabId = ($AppUI->getState('ProjIdxTab') !== null ? $AppUI->getState('ProjIdxTab') : 0);

//Lets fix the status filter for Not defined, All, All Active and Archived
//All
if ($currentTabId == 0 || $currentTabId == -1) {
	$project_status_filter = -1;
//All Active
} elseif ($currentTabId == 1) {
	$project_status_filter = -2;
//Archived
} elseif ($currentTabId == count($project_types) - 1) {
	$project_status_filter = -3;
	//The other project status
} else {
	$project_status_filter = ($projectTypes[0] ? $currentTabId - 2 : $currentTabId - 1);
}

$show_all_projects = false;
//If we are on All, All active or Archived then show the Status column
if (($project_status_filter == -1 || $project_status_filter == -2 || $project_status_filter == -3)) {
	$show_all_projects = true;
}

//Lets remove the unnecessary projects:
//All
if ($project_status_filter == -1) {
	//Don't do nothing because we are going to show evey project
	//All active
} elseif ($project_status_filter == -2) {
	$key = 0;
	foreach ($projects as $project) {
		if (!$project['project_active']) {
			unset($projects[$key]);
		}
		$key++;
	}
	$key = 0;
	foreach ($projects as $project) {
		$tmp_projects[$key] = $project;
		$key++;
	}
	$projects = $tmp_projects;
	//Archived
} elseif ($project_status_filter == -3) {
	$key = 0;
	foreach ($projects as $project) {
		if ($project['project_active']) {
			unset($projects[$key]);
		}
		$key++;
	}
	$key = 0;
	foreach ($projects as $project) {
		$tmp_projects[$key] = $project;
		$key++;
	}
	$projects = $tmp_projects;
	//The Status themselves
} else {
}

?>

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="prjprint">
<tr>
    <th nowrap="nowrap">
    	<?php echo $AppUI->_('Color'); ?>
    </th>
        <th nowrap="nowrap">
		<?php echo $AppUI->_('P'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('ID'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Project Name'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Company'); ?>
	</th>
        <th nowrap="nowrap">
		<?php echo $AppUI->_('Start'); ?>
	</th>
        <th nowrap="nowrap">
		<?php echo $AppUI->_('End'); ?>
	</th>
        <th nowrap="nowrap">
		<?php echo $AppUI->_('Actual'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Owner'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Tasks'); ?>
		(<?php echo $AppUI->_('My'); ?>)
	</th>
	<?php
if ($project_status_filter < 0) {
?>
		<th nowrap="nowrap">
			<?php echo $AppUI->_('Status'); ?>
		</th>
		<?php
}
?>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
$none = true;

//print_r($currentTabId.'.'.$show_all_projects.'.'.count($project_types).'.'.$project_status_filter);

foreach ($projects as $row) {
	if (($show_all_projects || ($row['project_active'] && $row['project_status'] == $project_status_filter)) || //tabbed view
		(($row['project_active'] && $row['project_status'] == $project_status_filter)) || //flat active projects
		((!$row['project_active'] && $project_status_filter == -3)) //flat archived projects
		) {
		$none = false;
		$start_date = intval(@$row['project_start_date']) ? new CDate($row['project_start_date']) : null;
		$end_date = intval(@$row['project_end_date']) ? new CDate($row['project_end_date']) : null;
		$adjusted_end_date = intval(@$row['project_end_date_adjusted']) ? new CDate($row['project_end_date_adjusted']) : null;
		$actual_end_date = intval(@$row['project_actual_end_date']) ? new CDate($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr>';
		$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#' . $row['project_color_identifier'] . '">';
		$s .= $CT . '<font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf("%.1f%%", $row['project_percent_complete']) . '</font>';
		$s .= $CR . '</td>';

		$s .= $CR . '<td align="center">';
		if ($row['project_priority'] < 0) {
			$s .= '<img src="' . w2PfindImage('icons/priority-' . -$row['project_priority'] . '.gif') . '" width=13 height=16>';
		} else
			if ($row['project_priority'] > 0) {
				$s .= '<img src="' . w2PfindImage('icons/priority+' . $row['project_priority'] . '.gif') . '"  width=13 height=16>';
			}
		$s .= $CR . '</td>';

		$s .= $CR . '<td nowrap="nowrap">';
		$s .= $CT . $row['project_id'];
		$s .= $CR . '</td>';

		$s .= $CR . '<td width="40%">';
		$s .= $CT . htmlspecialchars($row['project_name']);
		$s .= $CR . '</td>';

		$s .= $CR . '<td width="30%">';
		$s .= $CT . htmlspecialchars($row['company_name'], ENT_QUOTES);

		$s .= $CR . '</td>';

		$s .= $CR . '<td align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">' . ($end_date ? $end_date->format($df) : '-') . '</td>';
		$s .= $CR . '<td align="center">';
		$s .= $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
		$s .= $CR . '</td>';

		$s .= $CR . '<td nowrap="nowrap">' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row['total_tasks'] . ($row['my_tasks'] ? ' (' . $row['my_tasks'] . ')' : '');
		$s .= $CR . '</td>';

		if ($show_all_projects) {
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $row['project_status'] == 0 ? $AppUI->_('Not Defined') : $projectTypes[$row['project_status']];
			$s .= $CR . '</td>';
		}

		$s .= $CR . '</tr>';
		echo $s;

		echo '<tr><td height="1" colspan="12" style="border-bottom: 1px solid;padding:0px;" bgcolor="#FFFFFF"><img src="' . w2PfindImage('shim.gif') . '"></td></tr>';
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="10">' . $AppUI->_('No projects available') . '</td></tr>';
}
?>
	</td>
</tr>
</table>