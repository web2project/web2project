<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

/**
 * Generates a report of the task logs for given dates
 */
global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

$perms = &$AppUI->acl();
if (!canView('task_log')) {
	$AppUI->redirect(ACCESS_DENIED);
}
$do_report = w2PgetParam($_GET, 'do_report', 0);
$log_all = w2PgetParam($_GET, 'log_all', 0);
$log_pdf = w2PgetParam($_GET, 'log_pdf', 0);
$log_ignore = w2PgetParam($_GET, 'log_ignore', 0);
$log_userfilter = w2PgetParam($_GET, 'log_userfilter', '0');

$log_start_date = w2PgetParam($_GET, 'log_start_date', 0);
$log_end_date = w2PgetParam($_GET, 'log_end_date', 0);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

// Lets check cost codes
$q = new w2p_Database_Query;
$q->addTable('billingcode');
$q->addQuery('billingcode_id, billingcode_name');

$task_log_costcodes[0] = $AppUI->_('None');
$rows = $q->loadList();
echo db_error();
$nums = 0;
if ($rows) {
	$nums = count($rows);
}
foreach ($rows as $row) {
	$task_log_costcodes[$row['billingcode_id']] = $row['billingcode_name'];
}

echo $AppUI->getTheme()->styleRenderBoxTop();
?>

<form name="editFrm" action="" method="get" accept-charset="utf-8">
    <input type="hidden" name="m" value="reports" />
    <input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
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
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" alt="<?php echo $AppUI->_('Calendar'); ?>" />
                </a>
            </td>

            <td nowrap="nowrap">
                <?php echo $AppUI->_('User'); ?>:
                <select name="log_userfilter" class="text" style="width: 200px">
                    <?php
                    //TODO: don't we have a function to simplify this?
                    if ($log_userfilter == 0)
                        echo '<option value="0" selected="selected">' . $AppUI->_('All users') . '</option>';
                    else
                        echo '<option value="0">All users</option>';

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

            <td nowrap="nowrap">
                <input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
                <?php echo $AppUI->_('Log All'); ?>
            </td>

            <td nowrap="nowrap">
                <input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
                <?php echo $AppUI->_('Make PDF'); ?>
            </td>

            <td nowrap="nowrap">
                <input type="checkbox" name="log_ignore" />
                <?php echo $AppUI->_('Ignore 0 hours'); ?>
            </td>

            <td align="right" width="50%" nowrap="nowrap">
                <input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
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
		<td>';

	$q = new w2p_Database_Query;
	$q->addTable('task_log', 't');
	$q->addQuery('distinct(t.task_log_id), contact_display_name AS creator');
    $q->addQuery('billingcode_value, billingcode_name');
    $q->addQuery('ROUND((billingcode_value * t.task_log_hours), 2) AS amount');
    $q->addQuery('c.company_name, project_name');
    $q->addQuery('ts.task_name, task_log_task, task_log_hours, task_log_description, task_log_date');

	$q->addJoin('tasks', 'ts', 'ts.task_id = t.task_log_task');
	$q->addJoin('projects', '', 'projects.project_id = ts.task_project');
	$q->addJoin('users', 'u', 'user_id = task_log_creator');
	$q->addJoin('contacts', '', 'user_contact = contact_id');
	$q->addJoin('companies', 'c', 'c.company_id = projects.project_company');
	$q->leftJoin('billingcode', '', 'billingcode_id = task_log_costcode');
	$q->addJoin('project_departments', '', 'project_departments.project_id = projects.project_id');
	$q->addJoin('departments', '', 'department_id = dept_id');
	$q->addWhere('task_log_task > 0');

	if ($project_id) {
		$q->addWhere('projects.project_id = ' . (int)$project_id);
	}
	if ($company_id) {
		$q->addWhere('c.company_id = ' . (int)$company_id);
	}

	if (!$log_all) {
		$q->addWhere('task_log_date >= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'');
		$q->addWhere('task_log_date <= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'');
	}
	if ($log_ignore) {
		$q->addWhere('task_log_hours > 0');
	}
	if ($log_userfilter) {
		$q->addWhere('task_log_creator = ' . (int)$log_userfilter);
	}

	$proj = new CProject();
	$allowedProjects = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
	if (count($allowedProjects)) {
		$q->addWhere(implode(' AND ', $allowedProjects));
	}

	$q->addOrder('creator');
	$q->addOrder('company_name');
	$q->addOrder('project_name');
	$q->addOrder('task_log_date');

	$logs = $q->loadList();
	echo db_error();
?>
	<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th><?php echo $AppUI->_('Creator'); ?></th>
		<th><?php echo $AppUI->_('Company'); ?></th>
		<th><?php echo $AppUI->_('Project'); ?></th>
		<th><?php echo $AppUI->_('Task'); ?></th>
		<th><?php echo $AppUI->_('Date'); ?></th>
		<th><?php echo $AppUI->_('Description'); ?></th>
		<th><?php echo $AppUI->_('Billing Code'); ?></th>
		<th><?php echo $AppUI->_('Hours'); ?></th>
	</tr>
<?php
	$hours = 0.00;
	$tamount = 0.00;
	$pdfdata = array();

	foreach ($logs as $log) {
		$date = new w2p_Utilities_Date($log['task_log_date']);
		$hours += $log['task_log_hours'];
		$tamount += $log['amount'];

		$pdfdata[] = array($log['creator'], $log['company_name'], $log['project_name'], $log['task_name'], $date->format($df), $log['task_log_description'], $log['billingcode_name'], sprintf("%.2f", $log['task_log_hours']), );
?>
	<tr>
		<td><?php echo $log['creator']; ?></td>
		<td><?php echo $log['company_name']; ?></td>
		<td><?php echo $log['project_name']; ?></td>
		<td><a href="?m=tasks&amp;a=view&amp;task_id=<?php echo $log['task_log_task']; ?>"><?php echo $log['task_name']; ?></a></td>
		<td><?php echo $date->format($df); ?></td>
		<td><?php
		// dylan_cuthbert: auto-transation system in-progress, leave these lines for time-being
		$transbrk = "\n[translation]\n";
		$descrip = mb_str_replace("\n", '<br />', $log['task_log_description']);
		$tranpos = mb_strpos($descrip, mb_str_replace("\n", '<br />', $transbrk));
		if ($tranpos === false)
			echo '<a href="?m=tasks&amp;a=view&amp;task_id=' . $log['task_log_task'] . '&amp;tab=1&amp;task_log_id=' . $log['task_log_id'] . '#log">' . $descrip . '</a>';
		else {
			$descrip = mb_substr($descrip, 0, $tranpos);
			$tranpos = mb_strpos($log['task_log_description'], $transbrk);
			$transla = mb_substr($log['task_log_description'], $tranpos + mb_strlen($transbrk));
			$transla = mb_trim(mb_str_replace("'", '"', $transla));
			echo '<a href="?m=tasks&amp;a=view&amp;task_id=' . $log['task_log_task'] . '&amp;tab=1&amp;task_log_id=' . $log['task_log_id'] . '#log">' . $descrip . '</a><div style="font-weight: bold; text-align: right"><a title="' . $transla . '" class="hilite">[' . $AppUI->_('translation') . ']</a></div>';
		}
		// dylan_cuthbert; auto-translation end
?></td>
		<td><?php echo $log['billingcode_name']; ?></td>
		<td align="right"><?php printf('%.2f', $log['task_log_hours']); ?></td>
	</tr>
<?php
	}
	$pdfdata[] = array('', '', '', '', '', '', $AppUI->_('Totals') . ':', sprintf('%.2f', $hours), );
?>
	<tr>
		<td align="right" colspan="7"><?php echo $AppUI->_('Report Totals'); ?>:</td>
		<td align="right"><?php printf('%.2f', $hours); ?></td>
	</tr>
	</table>
<?php
	if ($log_pdf) {
		// make the PDF file
		if ($project_id) {
			$project = new CProject();
            $project->load($project_id);
			$pname = 'Project: ' . $project->project_name;
		} else {
			$pname = 'All Companies and All Projects';
		}

		if ($company_id) {
			$company = new CCompany();
            $company->load($company_id);
			$cname = 'Company: ' . $company->company_name;
		} else {
			$cname = 'All Companies and All Projects';
		}

		if ($log_userfilter) {
			$q = new w2p_Database_Query;
			$q->addTable('contacts');
			$q->addQuery('contact_display_name');
			$q->addJoin('users', '', 'user_contact = contact_id', 'inner');
			$q->addWhere('user_id =' . (int)$log_userfilter);
			$uname = 'User: ' . $q->loadResult();
		} else {
			$uname = 'All Users';
		}

        $output = new w2p_Output_PDFRenderer();
        $output->addTitle($AppUI->_('Task Log Report'));
        $output->addDate($df);

        $subtitle = ($company_id) ? $cname : $pname;
        $output->addSubtitle($subtitle);
        $output->addSubtitle($uname);

		if ($log_all) {
			$title = 'All Task Log entries';
		} else {
			$title = 'Task Log entries from ' . $start_date->format($df) . ' to ' . $end_date->format($df);
		}

		$pdfheaders = array($AppUI->_('Creator', UI_OUTPUT_JS), $AppUI->_('Company', UI_OUTPUT_JS), $AppUI->_('Project', UI_OUTPUT_JS), $AppUI->_('Task', UI_OUTPUT_JS), $AppUI->_('Date', UI_OUTPUT_JS), $AppUI->_('Description', UI_OUTPUT_JS), $AppUI->_('Billing Code', UI_OUTPUT_JS), $AppUI->_('Hours', UI_OUTPUT_JS), );

		$options = array('showLines' => 1, 'fontSize' => 7, 'rowGap' => 1, 'colGap' => 1, 'xPos' => 50, 'xOrientation' => 'right', 'width' => '500', 'cols' => array(0 => array('justification' => 'left', 'width' => 50), 1 => array('justification' => 'left', 'width' => 60), 2 => array('justification' => 'left', 'width' => 60), 3 => array('justification' => 'left', 'width' => 60), 4 => array('justification' => 'center', 'width' => 40), 5 => array('justification' => 'left', 'width' => 170), 6 => array('justification' => 'left', 'width' => 30), 7 => array('justification' => 'right', 'width' => 30), ));

        $output->addTable($title, $pdfheaders, $pdfdata, $options);

        $w2pReport = new CReport();
        if ($output->writeFile($w2pReport->getFilename())) {
            echo '<a href="' . W2P_BASE_URL . '/files/temp/' . $w2pReport->getFilename() . '.pdf" target="pdf">';
			echo $AppUI->_('View PDF File');
			echo '</a>';
		} else {
			echo 'Could not open file to save PDF.  ';
		}
	}
	echo '</td>
</tr>
</table>';
}
