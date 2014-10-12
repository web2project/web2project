<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $project, $project_statuses, $tab, $company_id, $owner, $project_type, $orderby, $orderdir, $m;

$currentTabId = $tab;
$is_tabbed = false;
$project_status_filter = $currentTabId - 1;

switch($tab) {
    case 0:
        //do nothing
        $filter = '1 = 1';
        break;
    case 1:
        $filter = 'project_active = 1';
        break;
    case count($project_statuses) - 1:
        $filter = 'project_active = 0';
        break;
    default:
        $filter = 'project_active = 1 AND project_status = ' . ($tab - 2);
}

$filter .= (($company_id > 0) ? ' AND project_company = ' . $company_id : '');
$filter .= (($owner > 0) ? ' AND project_owner = ' . $owner : '');
$filter .= (($project_type > -1) ? ' AND project_type = ' . $project_type : '');
$orderby = property_exists('CProject', $orderby) ? $orderby : 'project_name';
$orderby = ($orderby == 'project_company') ? 'company_name' : $orderby;

$projects = $project->loadAll($orderby . ' ' . $orderdir, $filter);
$projects = array_values($projects);

$fieldList = array();
$fieldNames = array();

$module = new w2p_System_Module();
$fields = $module->loadSettings('projects', 'index_list');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v2.3
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('project_color_identifier', 'project_priority',
        'project_name', 'project_company', 'project_start_date',
        'project_end_date', 'project_actual_end_date', 'project_owner', 'project_task_count');
    $fieldNames = array('%', 'P', 'Project Name', 'Company',
        'Start', 'End', 'Actual', 'Owner', 'Tasks');

    $module->storeSettings('projects', 'index_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

$page = (int) w2PgetParam($_GET, 'page', 1);
$paginator = new w2p_Utilities_Paginator($projects);
$items = $paginator->getItemsOnPage($page);

?>

<form action="./index.php?m=projects" method="post" accept-charset="utf-8">
    <table id="tblProjects-list" class="tbl list">
        <tr>
            <td colspan="<?php echo count($fieldNames) + 1; ?>">
                <?php echo $paginator->buildNavigation($AppUI, $m, $tab, array('orderby' => $orderby)); ?>
            </td>
        </tr>
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
		</tr>
		<?php
		$none = true;
		$projectArray = array();

        $project_types = w2PgetSysVal('ProjectType');
        $project_status = w2PgetSysVal('ProjectStatus');
        $customLookups = array('project_status' => $project_status, 'project_type' => $project_types);

		foreach ($items as $row) {
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
            if (!is_array($st_projects_arr)) {
                continue;
            }
            $htmlHelper = new w2p_Output_HTMLHelper($AppUI);

            foreach ($st_projects_arr as $st_project) {
                $multiproject_id = 0;
                $project_id = (isset($st_project[0])) ? $st_project[0]['project_id'] : 0;
                $level = $st_project[1];

                if ($project_id) {
                    if ($is_tabbed) {
                        $row = $all_projects[getProjectIndex($all_projects, $project_id)];
                    } else {
                        $row = $items[getProjectIndex($items, $project_id)];
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
                                $s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" />&nbsp;';
                                $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">';
                                $s .= (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : w2PtoolTip($row[$field], $AppUI->_('No information available'), true));
                                $s .= $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
                            } elseif ($count_projects > 0 && !$level) {
                                $s .= w2PtoolTip($row[$field], nl2br($row['project_description']) .'<br />'.
                                        '<i>'.$AppUI->_('this project is a parent on a multi-project structure').'</i><br />'.
                                        '<i>'.$AppUI->_('click to show/hide its children').'</i>');
                                $s .= '<a href="javascript: void(0);" onclick="expand_collapse(\'multiproject_tr_' . $row["project_id"] . '_\', \'tblProjects\')">';
                                $s .= '<img id="multiproject_tr_' . $row["project_id"] . '__expand" src="' . w2PfindImage('icons/expand.gif') . '" />';
                                $s .= '<img id="multiproject_tr_' . $row["project_id"] . '__collapse" src="' . w2PfindImage('icons/collapse.gif') . '" style="display:none" /></a>&nbsp;';
                                $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">' . (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : '') . $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>' . w2PendTip();
                            } else {
                                $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $row["project_id"] . '">';
                                $s .= (nl2br($row['project_description']) ? w2PtoolTip($row[$field], nl2br($row['project_description']), true) : w2PtoolTip($row[$field], $AppUI->_('No information available'), true));
                                $s .= $row[$field] . (nl2br($row['project_description']) ? w2PendTip() : '') . '</a>';
                            }
                            $s .= '</td>';
                            break;
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

                $s .= '<td class="center"><input type="checkbox" name="project_ids[]" value="' . $row['project_id'] . '" /></td>';

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
        $pstatus = w2PgetSysVal('ProjectStatus');
		if ($none) {
			echo '<tr><td colspan="25">' . $AppUI->_('No projects to display for this Company, Owner and Type, or your Search returned no results. Please check the filters above and try again.') . '</td></tr>';
		} else {
			?>
				<tr>
					<td colspan="25" align="right">
                        <input type="submit" class="btn btn-primary btn-mini" value="<?php echo $AppUI->_('Update projects status'); ?>" />
                        <input type="hidden" name="dosql" value="do_project_action" />
                        <input type="hidden" name="m" value="projects" />
                        <?php echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', $project_status_filter + 1, true); ?>
					</td>
				</tr>
			<?php
			}
		?>
        <tr>
            <td colspan="<?php echo count($fieldNames) + 1; ?>">
                <?php echo $paginator->buildNavigation($AppUI, $m, $tab); ?>
            </td>
        </tr>
	</table>
</form>
<?php
if ($is_tabbed) {
	echo $pageNav;
}
