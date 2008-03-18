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
    <th nowrap="nowrap">
    	<a href="?m=projects&orderby=project_color_identifier" class="hdr"><?php echo $AppUI->_('Progress'); ?></a>
    </th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=project_name" class="hdr"><?php echo $AppUI->_('Project Name'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=company_name" class="hdr"><?php echo $AppUI->_('Company'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_start_date" class="hdr"><?php echo $AppUI->_('Start'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_end_date" class="hdr"><?php echo $AppUI->_('End'); ?></a>
	</th>
      <!-- <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_actual_end_date" class="hdr"><?php echo $AppUI->_('Actual'); ?></a>
	</th>-->
      <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_status" class="hdr"><?php echo $AppUI->_('Status'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=total_tasks" class="hdr"><?php echo $AppUI->_('Tasks'); ?></a>
		<a href="?m=projects&orderby=my_tasks" class="hdr">(<?php echo $AppUI->_('My'); ?>)</a>
	</th>
	<?php
//	if($show_all_projects){
?>
		<th nowrap="nowrap">
			<?php echo $AppUI->_('Type'); ?>
		</th>
		<?php
//	}
?>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=task_log_problem" class="hdr"><?php echo $AppUI->_('P'); ?></a>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Selection'); ?>
	</th>
</tr>

<?php
$CR = "\n";
$CT = "\n\t";
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
			//            	if ($show_all_projects || ($row_st->project_status == $project_status_filter)) {
			$none = false;
			$start_date = intval($row['project_start_date']) ? new CDate($row['project_start_date']) : null;
			$end_date = intval($row['project_end_date']) ? new CDate($row['project_end_date']) : null;
			// $actual_end_date = intval( $row['project_actual_end_date'] ) ? new CDate( $row['project_actual_end_date'] ) : null;
			// $style = (( $actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

			if ($level) { 
				$s = '<tr id="multiproject_' . $row['project_original_parent'] . '_' . $row['project_id'] . '" style="visibility:collapse">';
			} else {
				$s = '<tr>';
			}
			$s .= '<td width="65" align="center" style="border: outset #eeeeee 2px;background-color:#' . $row["project_color_identifier"] . '">';
			$s .= $CT . '<font color="' . bestColor($row['project_color_identifier']) . '">' . sprintf('%.1f%%', $row['project_percent_complete']) . '</font>';
			$s .= $CR . '</td>';

			$s .= $CR . '<td width="50%">';

			$q = new DBQuery();
			$q->addTable('projects');
			$q->addQuery('COUNT(project_id)');
			$q->addWhere('project_original_parent = ' . (int)$row['project_id']);
			$count_projects = $q->loadResult();

			if ($level) {
				$s .= $CT . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . '<img src="./images/corner-dots.gif" width="16" height="12" border="0">&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			} elseif ($count_projects > 1 && !$level) {
				$s .= $CT . '<a href="#fp' . $row['project_id'] . '" onClick="expand_multiproject(\'multiproject_' . $row['project_id'] . '\', \'tblProjects\')"><img id="multiproject_' . $row['project_id'] . '_expand" src="./images/icons/expand.gif" width="12" height="12" border="0"><img id="multiproject_' . $row['project_id'] . '_collapse" src="./images/icons/collapse.gif" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			} else {
				$s .= $CT . '<a href="./index.php?m=projects&a=view&project_id=' . $row['project_id'] . '" title="' . htmlspecialchars($row['project_description'], ENT_QUOTES) . '">' . $row['project_name'] . '</a>';
			}
			$s .= $CR . '</td>';

			$s .= $CR . '<td width="30%">';
			if ($perms->checkModuleItem('companies', 'access', $row['project_company'])) {
				$s .= $CT . '<a href="?m=companies&a=view&company_id=' . $row['project_company'] . '" title="' . htmlspecialchars($row['company_description'], ENT_QUOTES) . '">' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</a>';
			} else {
				$s .= $CT . htmlspecialchars($row['company_name'], ENT_QUOTES);
			}
			$s .= $CR . '</td>';

			$s .= $CR . '<td align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
			$s .= $CR . '<td align="center">' . ($end_date ? $end_date->format($df) : '-') . '</td>';
			/*$s .= $CR . '<td align="center">';
			$s .= $actual_end_date ? '<a href="?m=tasks&a=view&task_id='.$row["critical_task"].'">' : '';
			$s .= $actual_end_date ? '<span '. $style.'>'.$actual_end_date->format( $df ).'</span>' : '-';
			$s .= $actual_end_date ? '</a>' : '';*/
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $project_status[$row['project_status']];
			$s .= $CR . '</td>';
			$s .= $CR . '</td>';

			$s .= $CR . '<td nowrap="nowrap">' . htmlspecialchars($row['user_username'], ENT_QUOTES) . '</td>';
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $row['total_tasks'] . ($row['my_tasks'] ? ' (' . $row['my_tasks'] . ')' : '');
			$s .= $CR . '</td>';
			//		if($show_all_projects){
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $row['project_type'] == 0 ? $AppUI->_('Unknown') : $project_types[$row['project_type']];
			$s .= $CR . '</td>';
			//		}
			$s .= $CR . '<td align="center">';
			$s .= $row['task_log_problem'] ? '<a href="?m=tasks&a=index&f=all&project_id=' . $row['project_id'] . '">' : '';
			$s .= $row['task_log_problem'] ? dPshowImage('./images/icons/dialog-warning5.png', 16, 16, 'Problem', 'Problem') : '-';
			$s .= $CR . $row['task_log_problem'] ? '</a>' : '';
			$s .= $CR . '</td>';
			$s .= $CR . '<td align="center">';
			if ($perms->checkModuleItem('projects', 'edit', $row['project_id'])) {
				$s .= $CT . '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" />';
			} else {
				$s .= $CT . '&nbsp;';
			}
			$s .= $CR . '</td>';

			$s .= $CR . '</tr>';
			/*                        if (!$level && $count_projects>1) {
			$multiproject_id = $row['project_id'];
			$s .= $CR . '<div style="display: none;" id="multiproject_'.$multiproject_id.'">';
			}                                       		
			if (($count_projects==$st_projects_counter) && $level && $count_projects>1)      
			$s .= $CR . '</div>';      
			if ($count_projects>1)
			$st_projects_counter++;*/
			echo $s;
			//            	}
		}
		//$st_projects_counter = 1;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="20">' . $AppUI->_('No projects available') . '</td></tr>';
} else {
?>
<tr>
	<td colspan="20" align="right">
		<?php
	echo '<input type="submit" class="button" value="' . $AppUI->_('Update projects status') . '" />';
	echo '<input type="hidden" name="update_project_status" value="1" />';
	echo '<input type="hidden" name="m" value="projects" />';
	echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', 2, true);
	// 2 will be the next step
}
?>
	</td>
</tr>
</table>
</form>