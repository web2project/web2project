<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$task_id = (int) w2PgetParam($_GET, 'task_id', 0);
$task_log_id = (int) w2PgetParam($_GET, 'task_log_id', 0);
$obj = new CTask();

if (!$obj->load($task_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $obj->canEdit();
$canDelete = $obj->canDelete();


$tab = $AppUI->processIntState('TaskLogVwTab', $_GET, 'tab', 0);

/**
 * Clear any reminders
 * @todo THIS SHOULD NOT HAPPEN HERE.. VIEWING SHOULD BE IDEMPOTENT
 */
$reminded = (int) w2PgetParam($_GET, 'reminded', 0);
if ($reminded) {
	$obj->clearReminder();
}

//check permissions for the associated project
$canReadProject = canView('projects', $obj->task_project);

$users = $obj->assignees($task_id);

$durnTypes = w2PgetSysVal('TaskDurationType');
$task_types = w2PgetSysVal('TaskType');
$billingCategory = w2PgetSysVal('BudgetCategory');

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Task', 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCell();
if ($canReadProject) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_project, 'view this project');
}

if ($canEdit) {
    $titleBlock->addButton('new log',  '?m=tasks&a=view&task_id=' . $task_id . '&tab=1');
    $titleBlock->addButton('new link', '?m=links&a=addedit&task_id=' . $task_id . '&project_id=' . $obj->task_project);
    $titleBlock->addButton('new file', '?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $obj->task_id);
    $titleBlock->addButton('new task', '?m=tasks&a=addedit&task_project=' . $obj->task_project . '&task_parent=' . $task_id);

    if (!$obj->task_represents_project) {
	    $titleBlock->addCrumb('?m=tasks&a=addedit&task_id=' . $task_id, 'edit this task');
    }
}
if ($obj->task_represents_project) {
    $titleBlock->addCrumb('?m=projects&a=view&project_id=' . $obj->task_represents_project, 'view subproject');
}
if ($canDelete) {
	$titleBlock->addCrumbDelete('delete task', $canDelete, $msg);
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<script language="javascript" type="text/javascript">
function updateTask() {
	var f = document.editFrm;

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

<table class="std view tasks">
    <tr>
        <th colspan="2"><?php echo $obj->task_name; ?></th>
    </tr>
    <tr>
        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table width="100%" cellspacing="1" cellpadding="2" class="well">
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_project', $obj->task_project); ?>
                </tr>
                <?php if ($obj->task_parent != $obj->task_id) {
                    $obj_parent = new CTask();
                    $obj_parent->load($obj->task_parent);
                ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Parent'); ?>:</td>
                    <td><a href="<?php echo "./index.php?m=tasks&a=view&task_id=" . $obj_parent->task_id; ?>"><?php echo $obj_parent->task_name; ?></a></td>
                </tr>
                <?php } ?>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_owner', $obj->task_owner); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
                    <td>
                        <?php
                            $task_priotities = w2PgetSysVal('TaskPriority');
                            echo $AppUI->_($task_priotities[$obj->task_priority]);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_related_url', $obj->task_related_url); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone'); ?>:</td>
                    <td width="300">
                        <?php if ($obj->task_milestone) {
                            echo $AppUI->_('Yes');
                        } else {
                            echo $AppUI->_('No');
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
                    <td width="300"><?php echo ($obj->task_percent_complete) ? $obj->task_percent_complete : 0; ?>%</td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_hours_worked', $obj->task_hours_worked . ' ' . $AppUI->_('hours')); ?>
                </tr>
                <tr>
                    <td>
                        <strong><?php echo $AppUI->_('Dates and Targets'); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_start_datetime', $obj->task_start_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date'); ?>:</td>
                    <?php echo $htmlHelper->createCell('task_end_datetime', $obj->task_end_date); ?>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration'); ?>:</td>
                    <td width="300"><?php echo $obj->task_duration . ' ' . $AppUI->_($durnTypes[$obj->task_duration_type]); ?></td>
                </tr>
                <tr>
                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Type'); ?> :</td>
                    <?php echo $htmlHelper->createCell('task_type', $AppUI->_($task_types[$obj->task_type])); ?>
                </tr>
                <?php if (w2PgetConfig('budget_info_display', false)) { ?>
				<tr>
                    <td align="center" nowrap="nowrap"><?php echo $AppUI->_('Finances'); ?>:</td>
                    <td align="center" nowrap="nowrap">
                        <table cellspacing="1" cellpadding="2" border="0" width="100%">
                            <tr>
                                <td align="center">
                                    <?php echo $AppUI->_('Target Budgets'); ?>:
                                </td>
                                <td align="center">
                                    <?php echo $AppUI->_('Actual Costs'); ?>:
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table cellspacing="1" cellpadding="2" border="0" width="100%">
                                        <?php
                                        $totalBudget = 0;
                                        foreach ($billingCategory as $id => $category) {
                                            $amount = $obj->budget[$id]['budget_amount'];
                                            $totalBudget += $amount;
                                            ?>
                                            <tr>
                                                <td align="right" nowrap="nowrap">
                                                    <?php echo $AppUI->_($category); ?>
                                                </td>
                                                <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                    <?php echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM')); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td align="right" nowrap="nowrap">&nbsp;</td>
                                            <td align="right" nowrap="nowrap">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Total Budget'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <table cellspacing="1" cellpadding="2" border="0" width="100%">
                                        <?php
                                        $bcode = new CSystem_Bcode();
                                        $results = $bcode->calculateTaskCost($task_id);
                                        foreach ($billingCategory as $id => $category) {
                                            ?>
                                            <tr>
                                                <td align="right" nowrap="nowrap">
                                                    <?php echo $AppUI->_($category); ?>
                                                </td>
                                                <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                    <?php
                                                    $amount = 0;
                                                    if (isset($results[$id])) {
                                                        $amount = $results[$id];
                                                    }
                                                    echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM'));
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Unidentified Costs'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php 
                                                $otherCosts = 0;
                                                if (isset($results['otherCosts'])) {
                                                    $otherCosts = $results['otherCosts'];
                                                }
                                                echo formatCurrency($otherCosts, $AppUI->getPref('CURRENCYFORM'));
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right" nowrap="nowrap">
                                                <?php echo $AppUI->_('Total Cost'); ?>
                                            </td>
                                            <td nowrap="nowrap" style="text-align: left; padding-left: 40px;">
                                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                                <?php
                                                $totalCosts = 0;
                                                if (isset($results['totalCosts'])) {
                                                    $totalCosts = $results['totalCosts'];
                                                }
                                                echo formatCurrency($totalCosts, $AppUI->getPref('CURRENCYFORM'));
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php if (isset($results['uncountedHours']) && $results['uncountedHours']) { ?>
                            <tr>
                                <td colspan="2" align="center">
                                    <?php echo '<span style="float:right; font-style: italic;">'.$results['uncountedHours'].' hours without billing codes</span>'; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </td>

        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Assigned Users'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <?php
                $s = count($users) == 0 ? '<tr><td bgcolor="#ffffff">' . $AppUI->_('none') . '</td></tr>' : '';
                foreach ($users as $row) {
                    $s .= '<tr>';
                    $s .= '<td width=80%>';
                    $s .= w2p_email($row['user_email'], $row['contact_display_name']);
                    $s .= '</td>';
                    $s .= $htmlHelper->createCell('perc_assignment', $row['perc_assignment']);
                    $s .= '</tr>';
                }
                echo $s;
                ?>
            </table>
            <strong><?php echo $AppUI->_('Dependencies'); ?></strong>
            <table width="100%" cellspacing="1" cellpadding="2" class="tbl list well">
                <?php
                $taskDep = $obj->getDependencyList($task_id);
                $s = count($taskDep) == 0 ? '<tr><td>' . $AppUI->_('none') . '</td></tr>' :
                    '<tr><th>' . $AppUI->_('Task') . '</th>' .
                    '<th>' . $AppUI->_('Work') . '</th>' .
                    '<th>' . $AppUI->_('Start Date') . '</th>' .
                    '<th>' . $AppUI->_('End Date') . '</th></tr>';
                foreach ($taskDep as $key => $array) {
                    $htmlHelper->stageRowData($array);
                    $s .= '<tr>';
                    $s .= $htmlHelper->createCell('task_name', $array['task_name']);
                    $s .= $htmlHelper->createCell('task_percent_complete', $array['task_percent_complete']);
                    $s .= $htmlHelper->createCell('task_start_date', $array['task_start_date']);
                    $s .= $htmlHelper->createCell('task_end_date', $array['task_end_date']);
                    $s .= '</tr>';

                }
                echo $s;
                ?>
            </table>
            <strong><?php echo $AppUI->_('Tasks depending on this Task'); ?></strong>
            <table width="100%" cellspacing="1" cellpadding="2" class="tbl list well">
                <?php
                $dependingTasks = $obj->getDependentTaskList($task_id);
                $s = count($dependingTasks) == 0 ? '<tr><td>' . $AppUI->_('none') . '</td></tr>' :
                    '<tr><th>' . $AppUI->_('Task') . '</th>' .
                    '<th>' . $AppUI->_('Work') . '</th>' .
                    '<th>' . $AppUI->_('Start Date') . '</th>' .
                    '<th>' . $AppUI->_('End Date') . '</th></tr>';
                foreach ($dependingTasks as $key => $array) {
                    $htmlHelper->stageRowData($array);
                    $s .= '<tr>';
                    $s .= $htmlHelper->createCell('task_name', $array['task_name']);
                    $s .= $htmlHelper->createCell('task_percent_complete', $array['task_percent_complete']);
                    $s .= $htmlHelper->createCell('task_start_date', $array['task_start_date']);
                    $s .= $htmlHelper->createCell('task_end_date', $array['task_end_date']);
                    $s .= '</tr>';
                }
                echo $s;
                ?>
            </table>
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table width="100%" cellspacing="1" cellpadding="2" class="well">
                <tr>
                    <?php echo $htmlHelper->createCell('task_description', $obj->task_description); ?>
                </tr>
                <?php
                $depts = $obj->getTaskDepartments(null, $task_id);
                if (count($depts)) { ?>
                <tr>
                    <td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="3">
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
                $contacts = $obj->getContacts(null, $task_id);
                if (count($contacts)) {
                    echo '<tr><td colspan="3"><strong>' . $AppUI->_('Task Contacts') . '</strong></td></tr>';
                    echo '<tr><td colspan="3">';
                    echo $htmlHelper->renderContactTable('tasks', $contacts);
                    echo '</td></tr>';
                }

                $project = new CProject();
                $project->project_id = $obj->task_project;
                $contacts = $project->getContactList();
                if (count($contacts)) {
                    echo '<tr><td colspan="3"><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
                    echo '<tr><td colspan="3">';
                    echo $htmlHelper->renderContactTable('projects', $contacts);
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
