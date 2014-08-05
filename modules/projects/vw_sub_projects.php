<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $project;

$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');

$original_project_id = $project->project_original_parent;
$project->project_status = -1;
$st_projects_arr = $project->getStructuredProjects();


$module = new w2p_System_Module();
$fields = $module->loadSettings('projects', 'subproject_list');

if (0 == count($fields)) {
    $fieldList = array('project_name', 'project_company', 'project_start_date', 'project_end_date', 'project_priority', 'project_status');
    $fieldNames = array('Project', 'Company', 'Start', 'End', 'P', 'Status');

    $module->storeSettings('projects', 'subproject_list', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
$fieldList = array_keys($fields);
$fieldNames = array_values($fields);

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->addBefore('edit', 'project_id');

echo $listTable->startTable('list subprojects');
echo $listTable->buildHeader($fields);

$s = '';

$customLookups = array('project_status' => $projectStatus, 'project_priority' => $projectPriority);

if (is_array($st_projects_arr)) {
    foreach ($st_projects_arr as $project) {
        $line = $project[0];
        $level = $project[1];
        if ($line['project_id']) {
            $s_project = new CProject();
            $s_project->load($line['project_id']);

            $row = get_object_vars($s_project);
            $row['company_id'] = $row['project_company'];
            $listTable->stageRowData($row);

            $s  = '<tr>';
            $s .= '<td><a href="./index.php?m=projects&a=addedit&project_id=' . $s_project->project_id . '"><img src="' . w2PfindImage('icons/' . ($project_id == $s_project->project_id ? 'pin' : 'pencil') . '.gif') . '" /></a></td>';
            foreach ($fieldList as $field) {
                if ('project_name' == $field) {
                    $s .= '<td class="_name">';
                    if ($level) {
                        $s .= str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . w2PshowImage('corner-dots.gif', 16, 12) . '&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $s_project->project_id . '">' . $s_project->project_name . '</a>';
                    } else {
                        $s .= '<a href="./index.php?m=projects&a=view&project_id=' . $s_project->project_id . '">' . $s_project->project_name . '</a>';
                    }
                    $s .= '</td>';
                } else {
                    $s .= $listTable->createCell($field, $s_project->{$field}, $customLookups);
                }
            }
            $s .= '</tr>';

            echo $s;
        }
    }
}

?>
</table>
<table width="100%" border="0" cellpadding="5" cellspacing="1">
    <tr>
        <td align="center" colspan="20">
<?php
$src = "?m=projects&a=vw_sub_projects_gantt&suppressHeaders=1&showLabels=1&proFilter=&showInactive=1showAllGantt=1&original_project_id=$original_project_id&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.90) + '";
echo "<script>document.write('<img src=\"$src\">')</script>";
?>
        </td>
    </tr>
</table>