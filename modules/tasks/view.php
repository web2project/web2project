<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$task_id = (int) w2PgetParam($_GET, 'task_id', 0);
$task_log_id = (int) w2PgetParam($_GET, 'task_log_id', 0);
$reminded = (int) w2PgetParam($_GET, 'reminded', 0);

// check permissions for this record
$canRead = canView($m, $task_id);
$canEdit = canEdit($m, $task_id);
$canDelete = canDelete($m, $task_id);

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

$perms = &$AppUI->acl();

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CTask();
$obj->loadFull($AppUI, $task_id);

if (!$obj) {
	$AppUI->setMsg('Task');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

if (!$obj->canAccess($AppUI->user_id)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// Clear any reminders
if ($reminded) {
	$obj->clearReminder();
}

$tab = $AppUI->processIntState('TaskLogVwTab', $_GET, 'tab', 0);

// get the prefered date format
$sf = $df = $AppUI->getPref('SHDATEFORMAT');
//Also view the time
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$start_date = intval($obj->task_start_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($obj->task_start_date, '%Y-%m-%d %T')) : null;
$end_date = intval($obj->task_end_date) ? new w2p_Utilities_Date($AppUI->formatTZAwareTime($obj->task_end_date, '%Y-%m-%d %T')) : null;

//check permissions for the associated project
$canReadProject = canView('projects', $obj->task_project);

$users = $obj->getAssignedUsers($task_id);

$durnTypes = w2PgetSysVal('TaskDurationType');

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Task', 'applet-48.png', $m, $m . '.' . $a);
$titleBlock->addCell();
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new task') . '">', '', '<form action="?m=tasks&a=addedit&task_project=' . $obj->task_project . '&task_parent=' . $task_id . '" method="post" accept-charset="utf-8">', '</form>');
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new file') . '">', '', '<form action="?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $obj->task_id . '" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->addCrumb('?m=tasks', 'tasks list');
if ($canReadProject) {
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_project, 'view this project');
}
if ($canEdit && 0 == $obj->task_represents_project) {
	$titleBlock->addCrumb('?m=tasks&a=addedit&task_id=' . $task_id, 'edit this task');
}
//$obj->task_represents_project
if ($obj->task_represents_project) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_represents_project, 'view subproject');
}
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete task', $canDelete, $msg);
}
$titleBlock->show();

$task_types = w2PgetSysVal('TaskType');

?>
<script language="javascript" type="text/javascript">
function updateTask() {
	var f = document.editFrm;
	if (f.task_log_description.value.length < 1) {
        alert( '<?php echo $AppUI->_('tasksComment', UI_OUTPUT_JS); ?>' );
        f.task_log_description.focus();
        return;
    }
    <?php
    // security improvement:
    // some javascript functions may not appear on client side in case of user not having write permissions
    // else users would be able to arbitrarily run 'bad' functions
    if ($canEdit) {
    ?>
    if (isNaN( parseInt( f.task_log_percent_complete.value+0 ) )) {
        alert( '<?php echo $AppUI->_('tasksPercent', UI_OUTPUT_JS); ?>' );
        f.task_log_percent_complete.focus();
        return;
	} else if(f.task_log_percent_complete.value  < 0 || f.task_log_percent_complete.value > 100) {
        alert( '<?php echo $AppUI->_('tasksPercentValue', UI_OUTPUT_JS); ?>' );
        f.task_log_percent_complete.focus();
        return;
	}
    <?php } ?>
	f.submit();
}
<?php if ($canDelete) { ?>
function delIt() {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Task', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>


<form name="frmDelete" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_task_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
</form>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
    <tr valign="top">
        <td width="50%">
            <table width="100%" cellspacing="1" cellpadding="2">
                <tr>
                    <td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Details'); ?></strong></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                    <td style="background-color:#<?php echo $obj->project_color_identifier; ?>">
                        <font color="<?php echo bestColor($obj->project_color_identifier); ?>">
                            <?php if ($perms->checkModuleItem('projects', 'access', $obj->task_project)) { ?>
                                <?php echo "<a href='?m=projects&a=view&project_id=" . $obj->task_project . "'>" . htmlspecialchars($obj->project_name, ENT_QUOTES) . '</a>'; ?>
                            <?php } else { ?>
                                <?php echo htmlspecialchars($company_detail['company_name'], ENT_QUOTES); ?>
                            <?php } ?>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task'); ?>:</td>
                    <td class="hilite"><strong><?php echo $obj->task_name; ?></strong></td>
                </tr>
                <?php if ($obj->task_parent != $obj->task_id) {
                    $obj_parent = new CTask();
                    $obj_parent->load($obj->task_parent);
                ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Parent'); ?>:</td>
                    <td class="hilite"><a href="<?php echo "./index.php?m=tasks&a=view&task_id=" . $obj_parent->task_id; ?>"><?php echo $obj_parent->task_name; ?></a></td>
                </tr>
                <?php } ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <td class="hilite"> <?php echo $obj->username; ?></td>
                </tr>				<tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
                    <td class="hilite">
                        <?php
                            $task_priotities = w2PgetSysVal('TaskPriority');
                            echo $AppUI->_($task_priotities[$obj->task_priority]);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address'); ?>:</td>
                    <td class="hilite" width="300"><a href="<?php echo $obj->task_related_url; ?>" target="task<?php echo $task_id; ?>"><?php echo $obj->task_related_url; ?></a></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone'); ?>:</td>
                    <td class="hilite" width="300">
                        <?php if ($obj->task_milestone) {
                            echo $AppUI->_('Yes');
                        } else {
                            echo $AppUI->_('No');
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $obj->task_percent_complete; ?>%</td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $obj->task_hours_worked; ?></td>
                </tr>
                <tr>
                    <td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Dates and Targets'); ?></strong></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $start_date ? $start_date->format($df) : '-'; ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $end_date ? $end_date->format($df) : '-'; ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration'); ?>:</td>
                    <td class="hilite" width="300"><?php echo $obj->task_duration . ' ' . $AppUI->_($durnTypes[$obj->task_duration_type]); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?>:</td>
                    <td class="hilite" width="300">
                        <?php
                            echo $w2Pconfig['currency_symbol'];
                            echo formatCurrency($obj->task_target_budget, $AppUI->getPref('CURRENCYFORM'));
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Type'); ?> :</td>
                    <td class="hilite" width="300"><?php echo $AppUI->_($task_types[$obj->task_type]); ?></td>
                </tr>
            </table>
        </td>

        <td width="50%">
            <table cellspacing="1" cellpadding="2" border="0" width="100%">
                <tr>
                    <td colspan="3"><strong><?php echo $AppUI->_('Assigned Users'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php
                            $s = '';
                            $s = count($users) == 0 ? '<tr><td bgcolor="#ffffff">' . $AppUI->_('none') . '</td></tr>' : '';
                            foreach ($users as $row) {
                                $s .= '<tr>';
                                $s .= '<td class="hilite"><a href="mailto:' . $row['user_email'] . '">' . $row['contact_display_name'] . '</a></td>';
                                $s .= '<td class="hilite center" align="right" width="20%">' . $row['perc_assignment'] . '%</td>';
                                $s .= '</tr>';
                            }
                            echo '<table width="100%" cellspacing="1" bgcolor="black">' . $s . '</table>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><strong><?php echo $AppUI->_('Dependencies'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3">
                    <?php
                        $taskDep = $obj->getDependencyList($task_id);
                        $s = count($taskDep) == 0 ? '<tr><td bgcolor="#ffffff">' . $AppUI->_('none') . '</td></tr>' : '';
                        foreach ($taskDep as $key => $array) {
                            $s .= '<tr><td class="hilite">';
                            $s .= '<a href="./index.php?m=tasks&a=view&task_id=' . $key . '">' . $array['task_name'] . '</a>';
							$s .= '</td><td class="hilite center" width="20%">';
							$s .= $array['task_percent_complete'];
                            $s .= '%</td></tr>';
                        }
                        echo '<table width="100%" cellspacing="1" bgcolor="black">' . $s . '</table>';
                    ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><strong><?php echo $AppUI->_('Tasks depending on this Task'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3">
                    <?php
                        $dependingTasks = $obj->getDependentTaskList($task_id);
                        $s = count($dependingTasks) == 0 ? '<tr><td bgcolor="#ffffff">' . $AppUI->_('none') . '</td></tr>' : '';
                        foreach ($dependingTasks as $key => $array) {
                            $s .= '<tr><td class="hilite">';
                            $s .= '<a href="./index.php?m=tasks&a=view&task_id=' . $key . '">' . $array['task_name'] . '</a>';
							$s .= '</td><td class="hilite center" width="20%">';
							$s .= $array['task_percent_complete'];
                            $s .= '%</td></tr>';
                        }
                        echo '<table width="100%" cellspacing="1" bgcolor="black">' . $s . '</table>';
                    ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" nowrap="nowrap">
                        <strong><?php echo $AppUI->_('Description'); ?></strong><br />
                    </td>
                 </tr>
                <tr>
                    <td class="hilite" colspan="3">
                        <?php echo w2p_textarea($obj->task_description); ?>
                    </td>
                </tr>
                <?php
                $depts = $obj->getTaskDepartments($AppUI, $task_id);
                if (count($depts)) { ?>
                <tr>
                    <td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="hilite">
                        <?php
                                foreach ($depts as $dept_id => $dept_info) {
                                    echo '<div>' . $dept_info['dept_name'];
                                    if ($dept_info['dept_phone'] != '') {
                                        echo '( ' . $dept_info['dept_phone'] . ' )';
                                    }
                                    echo '</div>';
                                }
                            ?>
                    </td>
                </tr>
                <?php }
                $contacts = $obj->getContacts($AppUI, $task_id);
                if (count($contacts)) {
                    echo '<tr><td><strong>' . $AppUI->_('Task Contacts') . '</strong></td></tr>';
                    echo '<tr><td colspan="3" class="hilite">';
                    echo w2p_Output_HTMLHelper::renderContactList($AppUI, $contacts);
                    echo '</td></tr>';
                }

                $contacts = CProject::getContacts($AppUI, $obj->task_project);
                if (count($contacts)) {
                    echo '<tr><td><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
                    echo '<tr><td colspan="3" class="hilite">';
                    echo w2p_Output_HTMLHelper::renderContactList($AppUI, $contacts);
                    echo '</td></tr>';
                }
                ?>
                <tr>
                    <td colspan="3">
                        <?php
                            $custom_fields = new w2p_Core_CustomFields($m, $a, $obj->task_id, 'view');
                            $custom_fields->printHTML();
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php
$query_string = '?m=tasks&a=view&task_id=' . $task_id;
$tabBox = new CTabBox('?m=tasks&a=view&task_id=' . $task_id, '', $tab);

$tabBox_show = 0;
if ($obj->task_dynamic != 1 && 0 == $obj->task_represents_project) {
	// tabbed information boxes
	$tabBox_show = 1;
	if (canView('task_log')) {
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_logs', 'Task Logs');
	}
	if ($task_log_id == 0) {
		if (canAdd('task_log')) {
			$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Log');
		}
	} elseif (canEdit('task_log')) {
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Edit Log');
	} elseif (canAdd('task_log')) {
		$tabBox_show = 1;
		$tabBox->add(W2P_BASE_DIR . '/modules/tasks/vw_log_update', 'Log');
	}
}

if (count($obj->getChildren()) > 0) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	// in the tasks file there is an if that checks
	// $_GET[task_status]; this patch is to be able to see
	// child tasks withing an inactive task
	$_GET['task_status'] = $obj->task_status;
	$tabBox->add(W2P_BASE_DIR . '/modules/tasks/tasks', 'Child Tasks');
}

if (count($tabBox->tabs)) {
	$tabBox_show = 1;
}

if ($tabBox_show == 1) {
	$tabBox->show();
}
