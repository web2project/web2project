<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $projects, $project_statuses, $project_status_filter, $currentTabId;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
// Let's check if the user submited the change status form

$projectStatuses = w2PgetSysVal('ProjectStatus');

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
} elseif ($currentTabId == count($project_statuses) - 1) {
	$project_status_filter = -3;
	//The other project status
} else {
	$project_status_filter = ($projectStatuses[0] ? $currentTabId - 2 : $currentTabId - 1);
}

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
}

$module = new w2p_System_Module();
$fields = $module->loadSettings('projects', 'printview');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v2.3
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_id', 'project_name', 'company_name', 'project_start_date',
        'project_end_date', 'project_actual_end_date', 'user_username',
        'project_task_count', 'project_status');
    $fieldNames = array('%', 'P', 'ID', 'Project Name',
        'Company', 'Start', 'End', 'Actual', 'Owner', 'Tasks', 'Status');

    $module->storeSettings('projects', 'printview', $fieldList, $fieldNames);
}
?>

<table cellpadding="3" cellspacing="1" class="prjprint">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php
$none = true;

$project_statuses = w2PgetSysVal('ProjectStatus');
$project_types = w2PgetSysVal('ProjectType');
$customLookups = array('project_status' => $project_statuses, 'project_type' => $project_types);

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

foreach ($projects as $row) {
	$htmlHelper->stageRowData($row);

    if (($show_all_projects || ($row['project_active'] && $row['project_status'] == $project_status_filter)) || //tabbed view
		(($row['project_active'] && $row['project_status'] == $project_status_filter)) || //flat active projects
		((!$row['project_active'] && $project_status_filter == -3)) //flat archived projects
		) {

		$none = false;
		$end_date = intval($row['project_end_date']) ? new w2p_Utilities_Date($row['project_end_date']) : null;
		$actual_end_date = intval($row['project_actual_end_date']) ? new w2p_Utilities_Date($row['project_actual_end_date']) : null;
		$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

		$s = '<tr><td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#' . $row['project_color_identifier'] . '"><font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf("%.1f%%", $row['project_percent_complete']) . '</font></td>';

        $s .= $htmlHelper->createCell('project_priority',        $row['project_priority']);
        $s .= $htmlHelper->createCell('project_id',              $row['project_id']);
        $s .= $htmlHelper->createCell('na',                      $row['project_name']);
        $s .= $htmlHelper->createCell('na',                      $row['company_name']);
        $s .= $htmlHelper->createCell('project_start_date',      $row['project_start_date']);
        $s .= $htmlHelper->createCell('project_end_date',        $row['project_end_date']);
        $s .= $htmlHelper->createCell('project_actual_end_date', $row['project_actual_end_date']);
        $s .= $htmlHelper->createCell('na',                      $row['owner_name']);
        $s .= $htmlHelper->createCell('project_task_count',      $row['project_task_count']);

		if ($show_all_projects) {
            $s .= $htmlHelper->createCell('project_status',      $row['project_status'], $customLookups);
		}

		$s .= '</tr>';
		echo $s;

		echo '<tr><td height="1" colspan="12" style="border-bottom: 1px solid;padding:0px;" bgcolor="#FFFFFF"><img src="' . w2PfindImage('shim.gif') . '" /></td></tr>';
	}
}
if ($none) {
	echo '<tr><td colspan="'.count($fieldList).'">' . $AppUI->_('No projects available') . '</td></tr>';
}
?>
</table>