<?php

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$df = $AppUI->getPref('SHDATEFORMAT');

?>
<table id="tblProjects" class="std view projects">
<tr>
    <td style="border: outset #d1d1cd 1px;background-color:#<?php echo $project->project_color_identifier; ?>" colspan="2" id="view-header">
        <?php
        echo '<font color="' . bestColor($project->project_color_identifier) . '"><strong>' . $project->project_name . '<strong></font>';
        ?>
    </td>
</tr>
<tr>
<td class="view-column">
    <strong><?php echo $AppUI->_('Details'); ?></strong>
    <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_company', $project->project_company); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?>:</td>
            <?php

            // TODO Need to rename field to avoid confusing HTMLhelper
            echo $htmlHelper->createCell('project_shortname', $project->project_short_name);
            ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_start_date', $project->project_start_date); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_end_date', $project->project_end_date); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual End Date'); ?>:</td>
            <td>
                <?php
                if ($project_id) {
                    echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
                    echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
                    echo $actual_end_date ? '</a>' : '';
                } else {
                    echo $AppUI->_('Dynamically calculated');
                }
                ?>
            </td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_owner', $project->project_owner); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_url', $project->project_url); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_demo_url', $project->project_demo_url); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_location', $project->project_location); ?>
        </tr>
        <tr>
            <td colspan="2">
                <?php
                $custom_fields = new w2p_Core_CustomFields($m, $a, $project->project_id, 'view');
                $custom_fields->printHTML();
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong><?php echo $AppUI->_('Description'); ?></strong><br />
                <table cellspacing="0" cellpadding="2" border="0" width="100%">
                    <tr>
                        <?php echo $htmlHelper->createCell('project_description', $project->project_description); ?>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</td>
<td class="view-column">
    <strong><?php echo $AppUI->_('Summary'); ?></strong><br />
    <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Status'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_status', $AppUI->_($pstatus[$project->project_status])); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_type', $AppUI->_($ptype[$project->project_type])); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?>:</td>
            <td width="100%" style="background-color:<?php echo $projectPriorityColor[$project->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$project->project_priority]); ?></td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_percent_complete', $project->project_percent_complete); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Active'); ?>:</td>
            <td width="100%"><?php echo $project->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Scheduled Hours'); ?>:</td>
            <?php echo $htmlHelper->createCell('total_hours', $project->project_scheduled_hours); ?>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Worked Hours'); ?>:</td>
            <?php echo $htmlHelper->createCell('project_worked_hours', $project->project_worked_hours); ?>
        </tr>
        <?php if (w2PgetConfig('budget_info_display', false)) { ?>
            <tr>
                <td align="center" nowrap="nowrap"><?php echo $AppUI->_('Finances'); ?>:</td>
                <td align="center" nowrap="nowrap">
                    <!--<table cellspacing="1" cellpadding="2" border="0" width="100%">
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
                                            $amount = 0;
                                            if (isset($project->budget[$id])) {
                                                $amount = $project->budget[$id]['budget_amount'];
                                            }
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
                                        $results = $bcode->calculateProjectCost($project_id);
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
                                            <td nowrap="nowrap" style="text-align: right; padding-left: 40px;">
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
                        </table>-->
                </td>
            </tr>
        <?php } ?>
        <?php
        $depts = $project->getDepartmentList();

        if (count($depts) > 0) { ?>
            <tr>
                <td><strong><?php echo $AppUI->_('Departments'); ?></strong></td>
            </tr>
            <tr>
                <td colspan='3'>
                    <?php
                    foreach ($depts as $dept_id => $dept_info) {
                        echo '<div>';
                        echo '<a href="?m=departments&a=view&dept_id='.$dept_id.'">'.$dept_info['dept_name'].'</a>';
                        if ($dept_info['dept_phone'] != '') {
                            echo '( ' . $dept_info['dept_phone'] . ' )';
                        }
                        echo '</div>';
                    }
                    ?>
                </td>
            </tr>
        <?php
        }

        $contacts = $project->getContactList();
        if (count($contacts)) {
            echo '<tr><td colspan="3"><strong>' . $AppUI->_('Project Contacts') . '</strong></td></tr>';
            echo '<tr><td colspan="3">';
            echo $htmlHelper->renderContactTable('projects', $contacts);
            echo '</td></tr>';
        }
        ?>
    </table>
</td>
</tr>
<?php
//lets add the subprojects table
$canReadMultiProjects = canView('projects');
if ($project->hasChildProjects($project_id) && $canReadMultiProjects) { ?>
    <tr>
        <td colspan="2">
            <?php
            echo w2PtoolTip('Multiproject', 'Click to Show/Hide Structure', true) . '<a href="javascript: void(0);" onclick="expand_collapse(\'multiproject\', \'tblProjects\')"><img id="multiproject_expand" src="' . w2PfindImage('icons/expand.gif') . '" /><img id="multiproject_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" style="display:none"></a>&nbsp;' . w2PendTip();
            echo '<strong>' . $AppUI->_('This Project is Part of the Following Multi-Project Structure') . ':<strong>';
            ?>
        </td>
    </tr>
    <tr id="multiproject" style="visibility:collapse;display:none;">
        <td colspan="2" class="hilite">
            <?php
            require W2P_BASE_DIR . '/modules/projects/vw_sub_projects.php';
            ?>
        </td>
    </tr>
<?php }
//here finishes the subproject structure
?>
</table>