<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $project_id;
global $st_projects_arr;

$df = $AppUI->getPref('SHDATEFORMAT');
$projectPriority = w2PgetSysVal('ProjectPriority');
$projectStatus = w2PgetSysVal('ProjectStatus');

$sp_obj = new CProject();
$sp_obj->load($project_id);
$original_project_id = $sp_obj->project_original_parent;
$structprojects = getStructuredProjects($original_project_id);
?>
<table width="100%" border="0" cellpadding="5" cellspacing="1" bgcolor="black">
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
if (is_array($st_projects_arr)) {
    foreach ($st_projects_arr as $project) {
        $line = $project[0];
        $level = $project[1];
        if ($line['project_id']) {
            $s_project = new CProject();
            $s_project->load($line['project_id']);
            $s_company = new CCompany();
            $s_company->load($s_project->project_company);
            $start_date = intval($s_project->project_start_date) ? new CDate($s_project->project_start_date) : null;
            $end_date = intval($s_project->project_end_date) ? new CDate($s_project->project_end_date) : null;
            $actual_end_date = intval($s_project->project_actual_end_date) ? new CDate($s_project->project_actual_end_date) : null;
            $style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';
            $x++;
            $row_class = ($x % 2) ? 'style="background:#fff;"' : 'style="background:#f0f0f0;"';
            $row_classr = ($x % 2) ? 'style="background:#fff;text-align:right;"' : 'style="background:#f0f0f0;text-align:right;"';
            $s .= '<tr><td ' . $row_class . ' align="center"><a href="./index.php?m=projects&a=addedit&project_id=' . $line['project_id'] . '"><img src="' . w2PfindImage('icons/' . ($project_id == $line['project_id'] ? 'pin' : 'pencil') . '.gif') . '" border="0" alt="" /></b></a></td>';
            $s .= '<td ' . $row_classr . ' nowrap="nowrap">' . $line['project_id'] . '</td>';
            if ($level) {
                $sd = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($level - 1)) . w2PshowImage('corner-dots.gif', 16, 12) . '&nbsp;' . '<a href="./index.php?m=projects&a=view&project_id=' . $line['project_id'] . '">' . $line['project_name'] . '</a>';
            } else {
                $sd = '<a href="./index.php?m=projects&a=view&project_id=' . $line['project_id'] . '">' . $line['project_name'] . '</a>';
            }
            $s .= '<td ' . $row_class . '>' . $sd . '</td>';
            $s .= '<td ' . $row_class . '><a href="./index.php?m=companies&a=view&company_id=' . $s_project->project_company . '">' . $s_company->company_name . '</a></td>';
            $s .= '<td ' . $row_class . ' align="center">' . ($start_date ? $start_date->format($df) : '-') . '</td>';
            $s .= '<td ' . $row_class . ' align="center">' . ($end_date ? $end_date->format($df) : '-') . '</td>';
            $s .= '<td ' . $row_class . ' align="center">' . $projectPriority[$s_project->project_priority] . '</td>';
            $s .= '<td ' . $row_class . ' align="center">' . $projectStatus[$s_project->project_status] . '</td></tr>';
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