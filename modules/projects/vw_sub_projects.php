<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $project;

$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');

$original_project_id = $project->project_original_parent;
$project->project_status = -1;
$st_projects_arr = $project->getStructuredProjects();
?>
<table cellpadding="5" cellspacing="0" class="list subprojects">
    <tr>
        <th width="12">&nbsp;</th>
        <th class="hilite" width="12"><?php echo $AppUI->_('ID'); ?></th>
        <th><?php echo $AppUI->_('Project'); ?></th>
        <th><?php echo $AppUI->_('Company'); ?></th>
        <th><?php echo $AppUI->_('Start'); ?></th>
        <th><?php echo $AppUI->_('End'); ?></th>
        <th><?php echo $AppUI->_('P'); ?></th>
        <th><?php echo $AppUI->_('Status'); ?></th>
    </tr>
<?php
$s = '';

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$customLookups = array('project_status' => $projectStatus, 'project_priority' => $projectPriority);

if (is_array($st_projects_arr)) {
    foreach ($st_projects_arr as $project) {
        $line = $project[0];
        $level = $project[1];
        if ($line['project_id']) {
            $s_project = new CProject();
            $s_project->loadFull(null, $line['project_id']);

            $row = get_object_vars($s_project);
            $row['company_id'] = $row['project_company'];
            $htmlHelper->stageRowData($row);

            $s .= '<tr><td class="data"><a href="./index.php?m=projects&a=addedit&project_id=' . $s_project->project_id . '"><img src="' . w2PfindImage('icons/' . ($project_id == $s_project->project_id ? 'pin' : 'pencil') . '.gif') . '" border="0" alt="" /></a></td>';
            $s .= '<td class="data">' . $s_project->project_id . '</td>';
            if ($level) {
                $sd = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . w2PshowImage('corner-dots.gif', 16, 12) . '&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $s_project->project_id . '">' . $s_project->project_name . '</a>';
            } else {
                $sd = '<a href="./index.php?m=projects&a=view&project_id=' . $s_project->project_id . '">' . $s_project->project_name . '</a>';
            }
            $s .= '<td class="data _name">' . $sd . '</td>';
            $s .= $htmlHelper->createCell('company_name', $s_project->company_name);
            $s .= $htmlHelper->createCell('project_start_date', $s_project->project_start_date);
            $s .= $htmlHelper->createCell('project_end_date', $s_project->project_end_date);
            $s .= $htmlHelper->createCell('project_priority', $s_project->project_priority, $customLookups);
            $s .= $htmlHelper->createCell('project_status', $s_project->project_status, $customLookups);
            $s .= '</tr>';
        }
    }
}
echo $s;
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