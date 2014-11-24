<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $cal_sdf;

$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = w2PgetParam($_POST['log_all'], 0);
$use_period = w2PgetParam($_POST, 'use_period', 0);
$display_week_hours = w2PgetParam($_POST, 'display_week_hours', 0);
$max_levels = w2PgetParam($_POST, 'max_levels', 'max');
$log_userfilter = w2PgetParam($_POST, 'log_userfilter', '');
$log_open = w2PgetParam($_POST, 'log_open', 0);
$pdf_output = w2PgetParam($_POST, 'pdf_output', 0);

$table_header = '';
$table_rows = '';

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
    $start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

echo $AppUI->getTheme()->styleRenderBoxTop();
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <input type="hidden" name="datePicker" value="log" />

    <table class="std">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td nowrap="nowrap">
                <select name="log_userfilter" class="text" style="width: 200px">
                    <?php
                    if ($log_userfilter == 0)
                        echo '<option value="0" selected="selected">' . $AppUI->_('All users');
                    else
                        echo '<option value="0">All users';

                    if (($log_userfilter_users = w2PgetUsersList())) {
                        foreach ($log_userfilter_users as $row) {
                            $selected = '';
                            if ($log_userfilter == $row['user_id']) {
                                $selected = ' selected="selected"';
                            }
                            echo '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['contact_first_name'] . ' ' . $row['contact_last_name'];
                        }
                    }
                    ?>
                 </select>
            </td>

            <td nowrap="nowrap" rowspan="2">
                <table>
                <tr><td>
                    <input type="checkbox" name="use_period" id="use_period" <?php if ($use_period) echo 'checked="checked"' ?> />
                    <label for="use_period"><?php echo $AppUI->_('Use the period'); ?></label>
                </td></tr>
                <tr><td>
                    <input type="checkbox" name="display_week_hours" id="display_week_hours" <?php if ($display_week_hours) echo 'checked="checked"' ?> />
                    <label for="display_week_hours"><?php echo $AppUI->_('Display allocated hours/week'); ?></label>
                </td></tr>
                </table>
            </td>

            <td align="right" width="50%" nowrap="nowrap">
                <input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
            </td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('to:'); ?></td>
            <td>
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>
            <td>
                <?php echo $AppUI->_('Levels to display'); ?>
                <input type="text" name="max_levels" size="10" maxlength="3" <?php $max_levels ?> />
            </td>
        </tr>
    </table>
</form>
<?php
if ($do_report) {

    echo $AppUI->getTheme()->styleRenderBoxBottom();
    echo '<br />';
    echo $AppUI->getTheme()->styleRenderBoxTop();
    echo '<table class="std">
	<tr>
		<td align="center">';

    // Let's figure out which users we have

    $user_list = w2PgetUsersHashList();
    if ($log_userfilter != 0) {
        $user_list = array($log_userfilter => $user_list[$log_userfilter]);
    }

    $ss = "'" . $start_date->format(FMT_DATETIME_MYSQL) . "'";
    $se = "'" . $end_date->format(FMT_DATETIME_MYSQL) . "'";

    $and = false;
    $where = false;

    $q = new w2p_Database_Query();
    $q->addTable('tasks', 't');
    $q->addQuery('t.*');
    $q->addJoin('projects', '', 'projects.project_id = task_project', 'inner');
    $q->addJoin('project_departments', '', 'project_departments.project_id = projects.project_id');
    $q->addJoin('departments', '', 'department_id = dept_id');
    $q->addWhere('project_active = 1');
    if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
        $q->addWhere('project_status <> ' . (int) $template_status);
    }

    if ($use_period) {
        $q->addWhere('( (task_start_date >= ' . $ss . ' AND task_start_date <= ' . $se . ') OR ' . '(task_end_date <= ' . $se . ' AND task_end_date >= ' . $ss . ') )');
    }

    if ($project_id != 0) {
        $q->addWhere('task_project=' . $project_id);
    }

    $proj = new CProject();
    $obj = new CTask();
    $allowedProjects = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
    $allowedTasks = $obj->getAllowedSQL($AppUI->user_id);

    if (count($allowedProjects)) {
        $q->addWhere(implode(' AND ', $allowedProjects));
    }

    if (count($allowedTasks)) {
        $q->addWhere(implode(' AND ', $allowedTasks));
    }

    $q->addOrder('task_end_date');

    $task_list_hash = $q->loadHashList('task_id');
    $q->clear();
    $task_list = array();
    $task_assigned_users = array();
    $i = 0;
    foreach ($task_list_hash as $task_id => $task_data) {
        $task = new CTask();
        $task->bind($task_data);
        $task_list[$i] = $task;
        $task_assigned_users[$i] = $task->assignees($task_id);
        $i++;
    }
    $Ntasks = $i;

    $user_usage = array();
    $task_dates = array();

    $actual_date = $start_date;
    $days_header = ""; // we will save days title here

    if (strtolower($max_levels) == 'max') {
        $max_levels = -1;
    } elseif ($max_levels == '') {
        $max_levels = -1;
    } else {
        $max_levels = atoi($max_levels);
    }
    if ($max_levels == 0) {
        $max_levels = 1;
    }
    if ($max_levels < 0) {
        $max_levels = -1;
    }

    if (count($task_list) == 0) {
        echo '<p>' . $AppUI->_('No data available') . '</p>';
    } else {

        $sss = $ss;
        $sse = $se;
        if (!$use_period) {
            $sss = -1;
            $sse = -1;
        }
        if ($display_week_hours and !$use_period) {
            foreach ($task_list as $t) {
                if ($sss == -1) {
                    $sss = $t->task_start_date;
                    $sse = $t->task_end_date;
                } else {
                    if ($t->task_start_date < $sss) {
                        $sss = $t->task_start_date;
                    }
                    if ($t->task_end_date > $sse) {
                        $sse = $t->task_end_date;
                    }
                }
            }
        }

        $table_header = '
			<tr>
				<th>' . $AppUI->_('Task') . '</th>' . ($project_id == 0 ? '<th>' . $AppUI->_('Project') . '</th>' : '') . '
				<th>' . $AppUI->_('Start Date') . '</th><th>' . $AppUI->_('End Date') . '</h>' .
                weekDates_r($display_week_hours, $sss, $sse) . '
			</tr>';
        $table_rows = '';

        foreach ($user_list as $user_id => $user_data) {

            $tmpuser = "<tr><td align='left' nowrap='nowrap' bgcolor='#D0D0D0'>" . $user_data["contact_display_name"] . '</td>';
            for ($w = 0, $w_cmp = (1 + ($project_id == 0 ? 1 : 0) + weekCells_r($display_week_hours, $sss, $sse)); $w <= $w_cmp; $w++) {
                $tmpuser .= '<td bgcolor="#D0D0D0">&nbsp;</td>';
            }
            $tmpuser .= '</tr>';

            $tmptasks = '';
            $actual_date = $start_date;
            foreach ($task_list as $task) {
                if (!isChildTask($task)) {
                    if (isMemberOfTask_r($task_list, $task_assigned_users, $Ntasks, $user_id, $task)) {
                        $tmptasks .= displayTask_r($task_list, $task, 0, $display_week_hours, $sss, $sse, !$project_id, $user_id);
                        // Get children
                        $tmptasks .= doChildren_r($task_list, $task_assigned_users, $Ntasks, $task->task_id, $user_id, 1, $max_levels, $display_week_hours, $sss, $sse, !$project_id);
                    }
                }
            }
            if ($tmptasks != '') {
                $table_rows .= $tmpuser;
                $table_rows .= $tmptasks;
            }
        }
    }
    echo '
	<table class="std">
		' . $table_header . $table_rows . '
	</table>
';
    echo '</td>
</tr>
</table>';
}
