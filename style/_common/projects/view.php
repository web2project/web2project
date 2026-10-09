<?php
/** @var w2p_Core_CAppUI $AppUI */

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$df = $AppUI->getPref('SHDATEFORMAT');

?>
<div id="tblProjects" class="card mb-3 std view projects">
    <div class="card-header fw-bold" id="view-header" style="background-color:#<?php echo $project->project_color_identifier; ?>; color:<?php echo bestColor($project->project_color_identifier); ?>">
        <?php echo $project->project_name; ?>
    </div>
    <div class="card-body" id="project">
    <div class="row g-3">
        <div class="col-lg-6 view-column">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <dl class="row mb-0 w2p-details well">
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Company'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_company', $project->project_company); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Short Name'); ?>:</dt>
                <dd class="col-sm-7"><?php
                    // TODO Need to rename field to avoid confusing HTMLhelper
                    echo $htmlHelper->createValue('project_shortname', $project->project_short_name);
                ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Start Date'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_start_date', $project->project_start_date); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Target End Date'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_end_date', $project->project_end_date); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Actual End Date'); ?>:</dt>
                <dd class="col-sm-7">
                    <?php
                    if ($project_id) {
                        echo $actual_end_date ? '<a href="?m=tasks&amp;a=view&amp;task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
                        echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
                        echo $actual_end_date ? '</a>' : '';
                    } else {
                        echo $AppUI->_('Dynamically calculated');
                    }
                    ?>
                </dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Project Owner'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_owner', $project->project_owner); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('URL'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_url', $project->project_url); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Staging URL'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_demo_url', $project->project_demo_url); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Project Location'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_location', $project->project_location); ?></dd>
            </dl>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $project->project_id, 'view');
            $custom_fields->printHTML();
            ?>
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <div class="w2p-description"><?php echo $htmlHelper->createValue('project_description', $project->project_description); ?></div>
        </div>
        <div class="col-lg-6 view-column">
            <strong><?php echo $AppUI->_('Summary'); ?></strong>
            <dl class="row mb-0 w2p-details well">
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Status'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_status', $AppUI->_($pstatus[$project->project_status])); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Type'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_type', $AppUI->_($ptype[$project->project_type])); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Priority'); ?>:</dt>
                <dd class="col-sm-7"><span class="px-1" style="background-color:<?php echo $projectPriorityColor[$project->project_priority] ?>"><?php echo $AppUI->_($projectPriority[$project->project_priority]); ?></span></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Progress'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_percent_complete', $project->project_percent_complete); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Active'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $project->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Scheduled Hours'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('total_hours', $project->project_scheduled_hours); ?></dd>
                <dt class="col-sm-5 text-sm-end"><?php echo $AppUI->_('Worked Hours'); ?>:</dt>
                <dd class="col-sm-7"><?php echo $htmlHelper->createValue('project_worked_hours', $project->project_worked_hours); ?></dd>
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
                                $amount = 0;
                                if (isset($project->budget[$id])) {
                                    $amount = $project->budget[$id]['budget_amount'];
                                }
                                $totalBudget += $amount;
                                ?>
                                <tr>
                                    <td class="text-end text-nowrap"><?php echo $AppUI->_($category); ?></td>
                                    <td class="text-end text-nowrap">
                                        <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;<?php echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM')); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td class="text-end text-nowrap"><?php echo $AppUI->_('Total Budget'); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;<?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
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
                            $results = $bcode->calculateProjectCost($project_id);
                            foreach ($billingCategory as $id => $category) {
                                $amount = isset($results[$id]) ? $results[$id] : 0;
                                ?>
                                <tr>
                                    <td class="text-end text-nowrap"><?php echo $AppUI->_($category); ?></td>
                                    <td class="text-end text-nowrap">
                                        <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;<?php echo formatCurrency($amount, $AppUI->getPref('CURRENCYFORM')); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td class="text-end text-nowrap"><?php echo $AppUI->_('Unidentified Costs'); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;<?php echo formatCurrency(isset($results['otherCosts']) ? $results['otherCosts'] : 0, $AppUI->getPref('CURRENCYFORM')); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end text-nowrap"><?php echo $AppUI->_('Total Cost'); ?></td>
                                <td class="text-end text-nowrap">
                                    <?php echo $w2Pconfig['currency_symbol'] ?>&nbsp;<?php echo formatCurrency(isset($results['totalCosts']) ? $results['totalCosts'] : 0, $AppUI->getPref('CURRENCYFORM')); ?>
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
            <?php
            $depts = $project->getDepartmentList();

            if (!is_null($depts) && count($depts) > 0) { ?>
                <strong><?php echo $AppUI->_('Departments'); ?></strong>
                <?php
                foreach ($depts as $dept_id => $dept_info) {
                    echo '<div>';
                    echo '<a href="?m=departments&amp;a=view&amp;dept_id='.$dept_id.'">'.$dept_info['dept_name'].'</a>';
                    if ($dept_info['dept_phone'] != '') {
                        echo '( ' . $dept_info['dept_phone'] . ' )';
                    }
                    echo '</div>';
                }
            }

            $contacts = $project->getContactList();
            if (count($contacts)) {
                echo '<strong>' . $AppUI->_('Project Contacts') . '</strong>';
                echo $htmlHelper->renderContactTable('projects', $contacts);
            }
            ?>
        </div>
    </div>
    </div>
    <?php
    //lets add the subprojects table
    $canReadMultiProjects = canView('projects');
    if ($project->hasChildProjects($project_id) && $canReadMultiProjects) { ?>
        <div class="card-footer">
            <?php
            echo w2PtoolTip('Multiproject', 'Click to Show/Hide Structure', true) . '<a href="#multiproject" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="multiproject"><img src="' . w2PfindImage('icons/expand.gif') . '" alt="" /></a>&nbsp;' . w2PendTip();
            echo '<strong>' . $AppUI->_('This Project is Part of the Following Multi-Project Structure') . ':</strong>';
            ?>
            <div id="multiproject" class="collapse hilite mt-2">
                <?php
                require W2P_BASE_DIR . '/modules/projects/vw_sub_projects.php';
                ?>
            </div>
        </div>
    <?php }
    //here finishes the subproject structure
    ?>
</div>
