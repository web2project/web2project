<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $projects, $pstatus, $project_statuses, $tab, $is_tabbed, $st_projects_arr;

$currentTabId = $tab;

$page = w2PgetParam($_GET, 'page', 1);
$xpg_pagesize = w2PgetConfig('page_size', 50);
$xpg_min = $xpg_pagesize * ($page - 1); // This is where we start our record set from

$projectStatuses = w2PgetSysVal('ProjectStatus');

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
	} elseif ($currentTabId == count($project_statuses) - 1) {
		$project_status_filter = -3;
		//The other project status
	} else {
		$project_status_filter = ($projectStatuses[0] ? $currentTabId - 2 : $currentTabId - 1);
	}

	$show_all_projects = false;
	//If we are on All, All active or Archived then show the Status column
	if (($project_status_filter == -1 || $project_status_filter == -2 || $project_status_filter == -3)) {
		$show_all_projects = true;
	}

	$all_projects = $projects;
    $tmp_projects = array();

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
	$pageNav = buildPaginationNav($AppUI, $m, $currentTabId, $xpg_totalrecs, $xpg_pagesize, $page);
    echo $pageNav;
} else {
	//flat view
	$project_status_filter = $currentTabId;

	if ($currentTabId == count($project_statuses) - 1) {
		$project_status_filter = -3;
		//The other project status
	} else {
		$project_status_filter = ($projectStatuses[0] ? $currentTabId : $currentTabId + 1);
	}
	$xpg_totalrecs = count($projects);
	$xpg_pagesize = count($projects);
}

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('projects', 'index_list');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v2.3
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'company_name', 'project_start_date',
        'project_end_date', 'project_actual_end_date', 'task_log_problem',
        'user_username', 'project_task_count');
    $fieldNames = array('%', 'P', 'Project Name', 'Company',
        'Start', 'End', 'Actual', 'LP', 'Owner', 'Tasks');

    $module = new w2p_Core_Module();
    $module->storeSettings('projects', 'index_list', $fieldList, $fieldNames);
}
?>

<form action="./index.php" method="get" accept-charset="utf-8">
    <table id="tblProjects-list" class="tbl list">
		<tr>
            <?php
            foreach ($fieldNames as $index => $name) {
                $column = ('project_color_identifier' == $fieldList[$index]) ? 'project_percent_complete' : $fieldList[$index];
                ?><th>
                    <a href="?m=projects&orderby=<?php echo $column; ?>" class="hdr">
                        <?php echo $AppUI->_($fieldNames[$index]); ?>
                    </a>
                </th><?php
            }
            ?>
			<th>
				<?php echo $AppUI->_('Selection'); ?>
			</th>
			<?php if ($show_all_projects) { ?>
				<th>
					<?php echo $AppUI->_('Status'); ?>
				</th>
			<?php } ?>
		</tr>
		<?php
		$none = true;
		$projectArray = array();

        $project_types = w2PgetSysVal('ProjectType');
        $project_status = w2PgetSysVal('ProjectStatus');
        $customLookups = array('project_status' => $project_status, 'project_type' => $project_types);

		for ($i = ($page - 1) * $xpg_pagesize; $i < $page * $xpg_pagesize && $i < $xpg_totalrecs; $i++) {
			$row = $projects[$i];
			if (($show_all_projects || ($row['project_active'] && $row['project_status'] == $project_status_filter) && $is_tabbed) || //tabbed view
				(($row['project_active'] && $row['project_status'] == $project_status_filter) && !$is_tabbed) || //flat active projects
				((!$row['project_active'] && $project_status_filter == -3) && !$is_tabbed) //flat archived projects
				) {

                $tmpProject = new CProject();

				$st_projects_arr = array();
				if ($row['project_id'] == $row['project_original_parent']) {
					$tmpProject->project_original_parent = $row['project_original_parent'];
                    $tmpProject->project_status = -1;
                    if ($project_status_filter == -2) {
						$st_projects_arr = $tmpProject->getStructuredProjects(true);
					} else {
						$st_projects_arr = $tmpProject->getStructuredProjects();
					}
				} else {
					$st_projects_arr[0][1] = 0;
				}

                $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
                if (!is_array($st_projects_arr)) {
                    continue;
                }
				foreach ($st_projects_arr as $st_project) {
                    $multiproject_id = 0;
                    $project_id = (isset($st_project[0])) ? $st_project[0]['project_id'] : 0;
					$level = $st_project[1];

					if ($project_id) {
						if ($is_tabbed) {
							$row = $all_projects[getProjectIndex($all_projects, $project_id)];
						} else {
							$row = $projects[getProjectIndex($projects, $project_id)];
						}
					}
					$none = false;
					$end_date = intval($row['project_end_date']) ? new w2p_Utilities_Date($row['project_end_date']) : null;
					$actual_end_date = intval($row['project_actual_end_date']) ? new w2p_Utilities_Date($row['project_actual_end_date']) : null;
					$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

					$s = '';
					if ($level) {
						$s .= '<tr style="display:none" id="multiproject_tr_' . $row['project_original_parent'] . '_' . $row['project_id'] . '_">';
						$s .= '<div id="multiproject_' . $row['project_original_parent'] . '_' . $row['project_id'] . '">';
					} else {
						$s .= '<tr>';
					}

                    $htmlHelper->stageRowData($row);
                    foreach ($fieldList as $field) {
                        $count_projects = $tmpProject->hasChildProjects($row['project_id']);

                        switch ($field) {
                            case 'project_name':
                                $s .= '<td width="40%" class="data _name">';
                                if ($level) {
                                    $s .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1));
                                    $s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0">&nbsp;';
                                    $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">';
                                    $s .= (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : w2PtoolTip($row[$field], $AppUI->_('No information available'), true));
                                    $s .= $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
                                } elseif ($count_projects > 0 && !$level) {
                                    $s .= w2PtoolTip($row[$field], nl2br($row['project_description']) .'<br />'.
                                            '<i>'.$AppUI->_('this project is a parent on a multi-project structure').'</i><br />'.
                                            '<i>'.$AppUI->_('click to show/hide its children').'</i>');
                                    $s .= '<a href="javascript: void(0);" onclick="expand_collapse(\'multiproject_tr_' . $row["project_id"] . '_\', \'tblProjects\')">';
                                    $s .= '<img id="multiproject_tr_' . $row["project_id"] . '__expand" src="' . w2PfindImage('icons/expand.gif') . '" width="12" height="12" border="0">';
                                    $s .= '<img id="multiproject_tr_' . $row["project_id"] . '__collapse" src="' . w2PfindImage('icons/collapse.gif') . '" width="12" height="12" border="0" style="display:none"></a>&nbsp;';
                                    $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">' . (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : '') . $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>' . w2PendTip();
                                } else {
                                    $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">';
                                    $s .= (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : w2PtoolTip($row[$field], $AppUI->_('No information available'), true));
                                    $s .= $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
                                }
                                $s .= '</td>';
                                break;
                            case 'department_list':
                            case 'project_departments':
                                $tmpProject->project_id = $row['project_id'];
                                $dept_array = $tmpProject->getDepartmentList();
                                $s .= '<td class="data _list">';
                                if (is_array($dept_array)) {
                                    foreach ($dept_array as $dept) {
                                        $s .= '<a href="?m=departments&a=view&dept_id='.$dept['dept_id'].'">';
                                        $s .= $dept['dept_name'];
                                        $s .= '</a>';
                                        $s .= '<br />';
                                    }
                                }
                                $s .= '</td>';
                                break;
                            default:
                                $s .= $htmlHelper->createCell($field, $row[$field], $customLookups);
                        }
                    }

					if ($show_all_projects) {
						$s .= '<td class="data _status" nowrap="nowrap">';
                        $s .= $AppUI->_($project_status[$row['project_status']]);
						$s .= '</td>';
					}
                    $s .= '<td class="center"><input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" /></td>';

					if ($level) {
						$s .= '</div>';
					}
                    $s .= '</tr>';

                    if (($project_id > 0 && !isset($projectArray[$project_id]))
                      || (!$project_id && !isset($projectArray[$row['project_id']]))) {
                        echo $s;
                    }
				}
			}
		}
		if ($none) {
			echo '<tr><td colspan="25">' . $AppUI->_('No projects to display for this Company, Owner and Type, or your Search returned no results. Please check the filters above and try again.') . '</td></tr>';
		} else {
			?>
				<tr>
					<td colspan="25" align="right">
                        <input type="submit" class="btn btn-primary btn-mini" value="<?php echo $AppUI->_('Update projects status'); ?>" />
                        <input type="hidden" name="update_project_status" value="1" />
                        <input type="hidden" name="m" value="projects" />
                        <?php echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', $project_status_filter + 1, true); ?>
					</td>
				</tr>
			<?php
			}
		?>
	</table>
</form>
<?php
if ($is_tabbed) {
	echo $pageNav;
}
