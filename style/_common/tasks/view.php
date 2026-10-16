<?php
/** @var w2p_Core_CAppUI $AppUI */

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

?>
<div class="card mb-3 std view tasks">
<div class="card-header fw-bold"><?php echo $obj->task_name; ?></div>
<div class="card-body">
<div class="row g-3">
<div class="col-lg-6 view-column">
    <strong><?php echo $AppUI->_('Details'); ?></strong>
    <dl class="row mb-2 w2p-details well">
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Project'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_project', $obj->task_project); ?></dd>
        <?php if ($obj->task_parent != $obj->task_id) {
            $obj_parent = new CTask();
            $obj_parent->load($obj->task_parent);
            ?>
            <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Task Parent'); ?>:</dt>
            <dd class="col-sm-7"><a href="<?php echo "./index.php?m=tasks&amp;a=view&amp;task_id=" . $obj_parent->task_id; ?>"><?php echo $obj_parent->task_name; ?></a></dd>
        <?php } ?>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Owner'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_owner', $obj->task_owner); ?></dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Priority'); ?>:</dt>
        <dd class="col-sm-7">
            <?php
            $task_priotities = w2PgetSysVal('TaskPriority');
            echo $AppUI->_($task_priotities[$obj->task_priority]);
            ?>
        </dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Web Address'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_related_url', $obj->task_related_url); ?></dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Milestone'); ?>:</dt>
        <dd class="col-sm-7">
            <?php if ($obj->task_milestone) {
                echo $AppUI->_('Yes');
            } else {
                echo $AppUI->_('No');
            } ?>
        </dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Progress'); ?>:</dt>
        <dd class="col-sm-7"><?php echo ($obj->task_percent_complete) ? $obj->task_percent_complete : 0; ?>%</dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Time Worked'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_hours_worked', $obj->task_hours_worked . ' ' . $AppUI->_('hours')); ?></dd>
    </dl>
    <strong><?php echo $AppUI->_('Dates and Targets'); ?></strong>
    <dl class="row mb-2 w2p-details well">
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Start Date'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_start_datetime', $obj->task_start_date); ?></dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Finish Date'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_end_datetime', $obj->task_end_date); ?></dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Expected Duration'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $obj->task_duration . ' ' . $AppUI->_($durnTypes[$obj->task_duration_type]); ?></dd>
        <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Task Type'); ?>:</dt>
        <dd class="col-sm-7"><?php echo $htmlHelper->createValue('task_type', $AppUI->_($task_types[$obj->task_type])); ?></dd>
    </dl>
    <?php if (w2PgetConfig('budget_info_display', false)) { ?>
        <strong><?php echo $AppUI->_('Finances'); ?></strong>
        <div class="row g-2">
            <div class="col-sm-6">
                <table class="tbl list">
                    <tr>
                        <th colspan="2"><?php echo $AppUI->_('Target Budgets'); ?>:</th>
                    </tr>
                    <?php
                    $totalBudget = 0;
                    foreach ($billingCategory as $id => $category) {
                        $amount = $obj->budget[$id]['budget_amount'];
                        $totalBudget += $amount;
                        ?>
                        <tr>
                            <td class="text-end text-nowrap">
                                <?php echo $AppUI->_($category); ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                                <?php echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM')); ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td class="text-end text-nowrap">
                            <?php echo $AppUI->_('Total Budget'); ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;
                            <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-sm-6">
                <table class="tbl list">
                    <tr>
                        <th colspan="2"><?php echo $AppUI->_('Actual Costs'); ?>:</th>
                    </tr>
                    <?php
                    $bcode = new CSystem_Bcode();
                    $results = $bcode->calculateTaskCost($task_id);
                    foreach ($billingCategory as $id => $category) {
                        ?>
                        <tr>
                            <td class="text-end text-nowrap">
                                <?php echo $AppUI->_($category); ?>
                            </td>
                            <td class="text-end text-nowrap">
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
                        <td class="text-end text-nowrap">
                            <?php echo $AppUI->_('Unidentified Costs'); ?>
                        </td>
                        <td class="text-end text-nowrap">
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
                        <td class="text-end text-nowrap">
                            <?php echo $AppUI->_('Total Cost'); ?>
                        </td>
                        <td class="text-end text-nowrap">
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
            </div>
            <?php if (isset($results['uncountedHours']) && $results['uncountedHours']) { ?>
                <div class="col-12 text-end fst-italic">
                    <?php echo $results['uncountedHours'] . ' hours without billing codes'; ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<div class="col-lg-6 view-column">
    <strong><?php echo $AppUI->_('Assigned Users'); ?></strong>
    <table class="tbl list well">
        <?php
        $s = count($users) == 0 ? '<tr><td>' . $AppUI->_('none') . '</td></tr>' : '';
        foreach ($users as $row) {
            $display = ('' == $row['contact_display_name']) ? $row['user_email'] : $row['contact_display_name'];
            $s .= '<tr>';
            $s .= '<td class="w-75">';
            $s .= w2p_email($row['user_email'], $display);
            $s .= '</td>';
            $s .= $htmlHelper->createCell('perc_assignment', $row['perc_assignment']);
            $s .= '</tr>';
        }
        echo $s;
        ?>
    </table>
    <strong><?php echo $AppUI->_('Dependencies'); ?></strong>
    <table class="tbl list well">
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
    <table class="tbl list well">
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
    <div class="mb-2 w2p-description well"><?php echo $htmlHelper->createValue('task_description', $obj->task_description); ?></div>
    <?php
    $depts = $obj->getTaskDepartments(null, $task_id);
    if (count($depts)) { ?>
        <strong><?php echo $AppUI->_('Departments'); ?></strong>
        <?php
        foreach ($depts as $dept_id => $dept_info) {
            echo '<div>' . $dept_info['dept_name'];
            if ($dept_info['dept_phone'] != '') {
                echo '( ' . $dept_info['dept_phone'] . ' )';
            }
            echo '</div>';
        }
    }
    $contacts = $obj->getContacts(null, $task_id);
    if (count($contacts)) {
        echo '<strong>' . $AppUI->_('Task Contacts') . '</strong>';
        echo $htmlHelper->renderContactTable('tasks', $contacts);
    }

    $project = new CProject();
    $project->project_id = $obj->task_project;
    $contacts = $project->getContactList();
    if (count($contacts)) {
        echo '<strong>' . $AppUI->_('Project Contacts') . '</strong>';
        echo $htmlHelper->renderContactTable('projects', $contacts);
    }

    $custom_fields = new w2p_Core_CustomFields($m, $a, $obj->task_id, 'view');
    $custom_fields->printHTML();
    ?>
</div>
</div>
</div>
</div>
