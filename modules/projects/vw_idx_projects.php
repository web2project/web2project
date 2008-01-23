<?php /* PROJECTS $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $projects, $company_id, $pstatus, $project_types, $currentTabId, $currentTabName, $is_tabbed, $st_projects_arr;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');

$page = w2PgetParam($_GET, 'page', 1);
$xpg_pagesize = 30;
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

//LIMIT ' . $xpg_min . ', ' . $xpg_pagesize ;
// counts total recs from selection

$projectTypes = w2PgetSysVal('ProjectStatus');

//Tabbed view
if ($is_tabbed) {
	$project_status_filter = $currentTabId;
	
	//Lets fix the status filter for Not defined, All, All Active and Archived
	//All
	if ($currentTabId == 0) {
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

	$all_projects = $projects;
	
	//Lets remove the unnecessary projects:
	if ($project_status_filter == -1) {
	//All
		//Don't do nothing because we are going to show evey project
	} elseif ($project_status_filter == -2) {
	//All active
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
	} elseif ($project_status_filter == -3) {
	//Archived
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
	} else {
	//The Status themselves
		$key = 0;
		foreach ($projects as $project) {
			if ($project['project_status'] != $project_status_filter || !$project['project_active']) {
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
	}

	$xpg_totalrecs = count($projects);

	// How many pages are we dealing with here ??
	$xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

	shownavbar_links_prj($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);
} else {
	//flat view
	$project_status_filter = $currentTabId;

	if ($currentTabId == count($project_types) - 1) {
		$project_status_filter = -3;
		//The other project status
	} else {
		$project_status_filter = ($projectTypes[0] ? $currentTabId : $currentTabId + 1);
	}
	$xpg_totalrecs = count($projects);
	$xpg_pagesize = count($projects);
}

?>

<form action='./index.php' method='get'>


<table id="tblProjects" width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
    <th nowrap="nowrap">
    	<a href="?m=projects&orderby=project_color_identifier" class="hdr"><?php echo $AppUI->_('Color'); ?></a>
	</th>
    <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_priority" class="hdr"><?php echo $AppUI->_('P'); ?></a>
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
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=project_actual_end_date" class="hdr"><?php echo $AppUI->_('Actual'); ?></a>
	</th>
        <th nowrap="nowrap">
		<a href="?m=projects&orderby=task_log_problem" class="hdr"><?php echo $AppUI->_('LP'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=user_username" class="hdr"><?php echo $AppUI->_('Owner'); ?></a>
	</th>
	<th nowrap="nowrap">
		<a href="?m=projects&orderby=total_tasks" class="hdr"><?php echo $AppUI->_('Tasks'); ?></a>
		<a href="?m=projects&orderby=my_tasks" class="hdr">(<?php echo $AppUI->_('My'); ?>)</a>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Selection'); ?>
	</th>
	<?php
if ($show_all_projects) {
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

//foreach ($projects as $row) {
for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
	$row = $projects[$i];

	if (($show_all_projects || ($row['project_active'] && $row['project_status'] == $project_status_filter) && $is_tabbed) || //tabbed view
		(($row['project_active'] && $row['project_status'] == $project_status_filter) && !$is_tabbed) || //flat active projects
		((!$row['project_active'] && $project_status_filter == -3) && !$is_tabbed) //flat archived projects
		) {

		//unset($st_projects_arr);
		$st_projects_arr = array();
		$sp_obj = new CProject();
		$sp_obj->load($project_id);
		if ($row['project_id'] == $row['project_original_parent']) {
			if ($project_status_filter == -2) {
				$structprojects = getStructuredProjects($row['project_original_parent'], '-1', true);
			} else {
				$structprojects = getStructuredProjects($row['project_original_parent'], '-1');
			}
		} else {
			$st_projects_arr[0][0] = $row_proj;
			$st_projects_arr[0][1] = 0;
		}

		$st_projects_counter = 1;
		foreach ($st_projects_arr as $st_project) {
			$multiproject_id = 0;
			$project = $st_project[0];
			$level = $st_project[1];
			if ($project['project_id']) {
				//$row_st = new CProject();
				//$row_st->load($project['project_id']);
				if ($is_tabbed) {
					$row = $all_projects[getProjectIndex($all_projects, $project['project_id'])];
				} else {
					$row = $projects[getProjectIndex($projects, $project['project_id'])];
				}
				//foreach(get_object_vars($row_st) as $field=>$data) {
				//	$row[$field] = $data;
				//}
			}
			$none = false;
			$start_date = intval(@$row['project_start_date']) ? new CDate($row['project_start_date']) : null;
			$end_date = intval(@$row['project_end_date']) ? new CDate($row['project_end_date']) : null;
			$actual_end_date = intval(@$row['project_actual_end_date']) ? new CDate($row['project_actual_end_date']) : null;
			$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

			$s = '';
			if ($level) {
				$s .= $CR . '<tr style="display:none" id="multiproject_tr_' . $row['project_original_parent'] . '_' . $row['project_id'] . '_">';
				$s .= $CR . '<div id="multiproject_' . $row['project_original_parent'] . '_' . $row['project_id'] . '">';
			} else {
				$s .= '<tr>';
			}
			$s .= '<td width="65" align="center" style="border: outset #eeeeee 1px;background-color:#' . $row['project_color_identifier'] . '">';
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

			$q = new DBQuery();
			$q->addTable('projects');
			$q->addQuery('COUNT(project_id)');
			$q->addWhere('project_original_parent = ' . $row['project_id']);
			$count_projects = $q->loadResult();

			if ($level) {
				$s .= $CT . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0">&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">' . (nl2br($row['project_description']) ? w2PtoolTip($row['project_name'], nl2br($row['project_description']), true) : '') . $row["project_name"] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
			} elseif ($count_projects > 1 && !$level) {
				$s .= $CT . w2PtoolTip('multi-project parent', 'this project is a parent on a multi-project structure<br />click to show/hide its children.') . '<a href="#fp' . $row["project_id"] . '" onclick="expand_collapse(\'multiproject_tr_' . $row["project_id"] . '_\', \'tblProjects\')"><img id="multiproject_tr_' . $row["project_id"] . '__expand" src="' . w2PfindImage('icons/expand.gif') . '" width="12" height="12" border="0"><img id="multiproject_tr_' . $row["project_id"] . '__collapse" src="' . w2PfindImage('icons/collapse.gif') . '" width="12" height="12" border="0" style="display:none"></a>&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">' . (nl2br($row['project_description']) ? w2PtoolTip($row['project_name'], nl2br($row['project_description']), true) : '') . $row['project_name'] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>' . w2PendTip();
			} else {
				$s .= $CT . '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">' . (nl2br($row['project_description']) ? w2PtoolTip($row['project_name'], nl2br($row['project_description']), true) : '') . $row["project_name"] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
			}
			$s .= $CR . '</td>';

			$s .= $CR . '<td width="30%">';
			$s .= $CT . '<a href="?m=companies&a=view&company_id=' . $row["project_company"] . '" ><span title="' . (nl2br(htmlspecialchars($row['company_description'])) ? htmlspecialchars($row['company_name'], ENT_QUOTES) . '::' . nl2br(htmlspecialchars($row['company_description'])) : '') . '" >' . htmlspecialchars($row['company_name'], ENT_QUOTES) . '</span></a>';
			$s .= $CR . '</td>';

			$s .= $CR . '<td nowrap="nowrap" align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
			$s .= $CR . '<td nowrap="nowrap" align="center">' . ($end_date ? $end_date->format($df) : '-') . '</td>';
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

			$s .= $CR . '<td nowrap="nowrap">' . htmlspecialchars($row['owner_name'], ENT_QUOTES) . '</td>';
			$s .= $CR . '<td align="center" nowrap="nowrap">';
			$s .= $CT . $row['total_tasks'] . ($row['my_tasks'] ? ' (' . $row['my_tasks'] . ')' : '');
			$s .= $CR . '</td>';
			$s .= $CR . '<td align="center">';
			$s .= $CT . '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" />';
			$s .= $CR . '</td>';

			if ($show_all_projects) {
				$s .= $CR . '<td align="center" nowrap="nowrap">';
				$s .= $CT . $row['project_status'] == 0 ? $AppUI->_('Not Defined') : ($projectTypes[0] ? $project_types[$row['project_status'] + 2] : $project_types[$row['project_status'] + 1]);
				$s .= $CR . '</td>';
			}

			if ($level) {
				//$s .= '			</tr>';
				//$s .= '		</table>';
				//$s .= '</td>';
				$s .= '</div>';
				$s .= '</tr>';
			} else {
				$s .= '</tr>';
			}
			echo $s;
		}
		//$st_projects_counter = 1;
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="12">' . $AppUI->_('No projects available') . '</td></tr>';
} else {
?>
<tr>
	<td colspan="12" align="right">
		<?php
	echo "<input type='submit' class='button' value='" . $AppUI->_('Update projects status') . "' />";
	echo "<input type='hidden' name='update_project_status' value='1' />";
	echo "<input type='hidden' name='m' value='projects' />";
	echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', $project_status_filter + 1, true);
	// 2 will be the next step
}
?>
	</td>
</tr>
</table>
</form>
<?php
if ($is_tabbed) {
	shownavbar_links_prj($xpg_totalrecs, $xpg_pagesize, $xpg_total_pages, $page);
}
?>