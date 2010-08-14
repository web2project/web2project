<?php /* $Id$ $URL$ */
global $AppUI, $projects, $company_id, $pstatus, $project_types, $currentTabId, $currentTabName, $st_projects_arr;

$check = $AppUI->_('All Projects', UI_OUTPUT_RAW);
$show_all_projects = false;
if (stristr($currentTabName, $check) !== false)
	$show_all_projects = true;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
// Let's check if the user submited the change status form

$project_status = dPgetSysVal('ProjectStatus');
$project_types = dPgetSysVal('ProjectType');

?>
<script type="text/JavaScript">

function expand_multiproject(id, table_name) {
      var trs = document.getElementsByTagName('tr');

      for (var i=0, i_cmp=trs.length;i < i_cmp;i++) {
	      var tr_name = trs.item(i).id;
	      if (tr_name.indexOf(id+'_') >= 0) {
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

<form name='frmProjects' action='./index.php' method='get'>


<table id="tblProjects" width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="right" width="65" nowrap="nowrap">&nbsp;<?php echo $AppUI->_('sort by'); ?>:&nbsp;</td>
</tr>
<tr>
    <?php
    $fieldList = array('project_color_identifier', 'task_log_problem', 
        'project_name', 'company_name', 'project_start_date', 'project_end_date',
        'project_status', 'user_username', 'total_tasks');
    $fieldNames = array('Progress', 'P', 'Project Name', 'Company', 'Start',
        'End', 'Status', 'Owner', 'Tasks');
    foreach ($fieldNames as $index => $name) {
        ?><th nowrap="nowrap">
            <a href="?m=projects&orderby=<?php echo $fieldList[$index]; ?>" class="hdr">
                <?php echo $AppUI->_($fieldNames[$index]); ?>
            </a>
        </th><?php
    }
    ?>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Type'); ?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Selection'); ?>
	</th>
</tr>

<?php
$none = true;

//Tabbed view
$project_status_filter = $currentTabId;
//Project not defined
if ($currentTabId == count($project_types) - 1)
	$project_status_filter = 0;

foreach ($projects as $row_proj) {
	if (!$perms->checkModuleItem('projects', 'view', $row_proj['project_id'])) {
		continue;
	}
	if (($row_proj['project_type'] == $currentTabId && $row_proj['project_type'] != 0) || (!$currentTabId && $row_proj['project_id'] == $row_proj['project_original_parent']) || ($currentTabId && $row_proj['project_type'] == 0 && $currentTabId == count($project_types))) {
		//unset($st_projects_arr);
		$st_projects_arr = array();
		$sp_obj = new CProject();
		$sp_obj->load($project_id);
		if ($row_proj['project_id'] == $row_proj['project_original_parent']) {
			$structprojects = getStructuredProjects($row_proj['project_original_parent']);
		} else {
			$st_projects_arr[0][0] = $row_proj;
			$st_projects_arr[0][1] = 0;
		}

		$tmpProject = new CProject();

		$st_projects_counter = 1;
		foreach ($st_projects_arr as $st_project) {
			$multiproject_id = 0;
			$project = $st_project[0];
			$level = $st_project[1];
			$row_st = new CProject();
			$row_st->load($project['project_id']);
			if (!$perms->checkModuleItem('projects', 'view', $row['project_id'])) {
				continue;
			}
			$row = $projects[$project['project_id']];
			$none = false;
			$start_date = intval($row['project_start_date']) ? new CDate($row['project_start_date']) : null;
			$end_date = intval($row['project_end_date']) ? new CDate($row['project_end_date']) : null;

			if ($level) { 
				$s = '<tr id="multiproject_' . $row['project_original_parent'] . '_' . $row['project_id'] . '" style="visibility:collapse">';
			} else {
				$s = '<tr>';
			}
			$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#' . $row["project_color_identifier"] . '"><font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</font></td><td width="50%">';

			$count_projects = $tmpProject->hasChildProjects($row['project_id']);

			if ($level) {
				$s .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . '<img src="./images/corner-dots.gif" width="16" height="12" border="0">&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			} elseif ($count_projects > 0 && !$level) {
				$s .= '<a href="javascript: void(0);" onClick="expand_multiproject(\'multiproject_' . $row['project_id'] . '\', \'tblProjects\')"><img id="multiproject_' . $row['project_id'] . '_expand" src="./images/icons/expand.gif" width="12" height="12" border="0"><img id="multiproject_' . $row['project_id'] . '_collapse" src="./images/icons/collapse.gif" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			} else {
				$s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			}
			$s .= '</td><td width="30%">';
			if ($perms->checkModuleItem('companies', 'access', $row['project_company'])) {
				$s .= '<a href="?m=companies&a=view&company_id=' . $row['project_company'] . '" title="' . htmlspecialchars($row['company_description'], ENT_QUOTES) . '">' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</a>';
			} else {
				$s .= htmlspecialchars($row['company_name'], ENT_QUOTES);
			}
			$s .= '</td><td align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td><td align="center">' . ($end_date ? $end_date->format($df) : '-') . '</td>';
			$s .= '<td align="center" nowrap="nowrap">' . $project_status[$row['project_status']] . '</td>';

			$s .= '<td nowrap="nowrap">' . htmlspecialchars($row['user_username'], ENT_QUOTES) . '</td><td align="center" nowrap="nowrap">';
			$s .= $row['total_tasks'] . ($row['my_tasks'] ? ' (' . $row['my_tasks'] . ')' : '');
			$s .= '</td><td align="center" nowrap="nowrap">';
			$s .= $row['project_type'] == 0 ? $AppUI->_('Unknown') : $project_types[$row['project_type']];
			$s .= '</td><td align="center">';
			$s .= $row['task_log_problem'] ? '<a href="?m=tasks&a=index&f=all&project_id=' . $row['project_id'] . '">' : '';
			$s .= $row['task_log_problem'] ? dPshowImage('./images/icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem') : '-';
			$s .= $row['task_log_problem'] ? '</a>' : '';
			$s .= '</td><td align="center">';
			if ($perms->checkModuleItem('projects', 'edit', $row['project_id'])) {
				$s .= '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" />';
			} else {
				$s .= '&nbsp;';
			}
			$s .= '</td></tr>';
			echo $s;
		}
	}
}
if ($none) {
	echo '<tr><td colspan="20">' . $AppUI->_('No projects available') . '</td></tr>';
} else {
?>
<tr>
	<td colspan="20" align="right">
		<?php
			$s = '<input type="submit" class="button" value="' . $AppUI->_('Update projects status') . '" />';
			$s .= '<input type="hidden" name="update_project_status" value="1" />';
			$s .= '<input type="hidden" name="m" value="projects" />';
			$s .= arraySelect($pstatus, 'project_status', 'size="1" class="text"', 2, true);
			echo $s;
	// 2 will be the next step
}
?>
	</td>
</tr>
</table>
</form>