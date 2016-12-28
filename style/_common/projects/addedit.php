<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit projects">
    <input type="hidden" name="dosql" value="do_project_aed" />
    <input type="hidden" name="project_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="project_creator" value="<?php echo is_null($object->project_creator) ? $AppUI->user_id : $object->project_creator; ?>" />
    <input type="hidden" name="project_contacts" id="project_contacts" value="<?php echo implode(',', $selected_contacts); ?>" />
    <input type="hidden" name="datePicker" value="project" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit projects">
        <div class="column left">
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php
                $options = array();
                $options['maxlength'] = 255;
                $options['onBlur'] = 'setShort()';
                $form->showField('project_name', $object->project_name, $options); ?>
            </p>
            <p>
                <?php $form->showLabel('Parent Project'); ?>
                <?php echo arraySelect($structprojects, 'project_parent', 'size="1" style="width:250px;" class="text"', $object->project_parent ? $object->project_parent : 0) ?>
            </p>
            <p>
                <?php $form->showLabel('Company'); ?>
                <?php echo arraySelect($companies, 'project_company', 'class="text" size="1"', $object->project_company); ?>
            </p>
            <?php
            if ($AppUI->isActiveModule('departments') && canAccess('departments')) {
                //Build display list for departments
                $company_id = $object->project_company;
                $selected_departments = array();
                if ($object_id) {
                    $myDepartments = $object->getDepartmentList();
                    $selected_departments = (count($myDepartments) > 0) ? array_keys($myDepartments) : array();
                }
                $departments_count = 0;
                $department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
                if ($department_selection_list != '' || $object_id) {
                    $department_selection_list = '<p>' . $form->addLabel('Departments') . '<select name="project_departments[]" multiple="multiple" class="text"><option value="0"></option>' . $department_selection_list . '</select></p>';
                } else {
                    $department_selection_list = '<input type="button" class="button" value="' . $AppUI->_('Select department...') . '" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments"';
                }
                // Let's check if the actual company has departments registered
                if ($department_selection_list != '') {
                    echo $department_selection_list;
                }
            }
            ?>
            <p>
                <?php $form->showLabel('Project Owner'); ?>
                <?php
                // pull users
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('projects');
                ?>
                <?php $form->showField('project_owner', $object->project_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Contacts'); ?>
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Select contacts...'); ?>" onclick="javascript:popContacts();" />
            </p>
            <p>
                <?php $form->showLabel('Start Date'); ?>
                <?php $form->showField('project_start_date', $object->project_start_date); ?>
            </p>
            <p>
                <?php $form->showLabel('Target Finish Date'); ?>
                <?php $form->showField('project_end_date', $object->project_end_date); ?>
            </p>
            <p>
                <?php $form->showLabel('Actual Finish Date'); ?>
                <?php
                if ($object_id) {
                    echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
                    echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
                    echo $actual_end_date ? '</a>' : '';
                } else {
                    echo $AppUI->_('Dynamically calculated');
                }
                ?>
            </p>
            <p>
                <?php $form->showLabel('Project Location'); ?>
                <?php $form->showField('project_location', $object->project_location, array('maxlength' => 50)); ?>
            </p>
            <?php if (w2PgetConfig('budget_info_display', false)) { ?>
                <p>
                    <?php $form->showLabel('Target Budgets'); ?>
                    &nbsp;
                </p>
                <?php
                $billingCategory = w2PgetSysVal('BudgetCategory');
                $totalBudget = 0;
                foreach ($billingCategory as $id => $category) {
                    $amount = 0;
                    if (isset($object->budget[$id])) {
                        $amount = $object->budget[$id]['budget_amount'];
                    }
                    $totalBudget += $amount;
                    ?>
                    <p>
                        <?php $form->showLabel($AppUI->_($category)); ?>
                        <?php echo $w2Pconfig['currency_symbol']; ?> <?php $form->showField("budget_$id", $amount, array('maxlength' => 15)); ?>
                    </p>
                <?php
                }
                ?>
                <p>
                    <?php $form->showLabel('Total Target Budget'); ?>
                    <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                </p>
                <p>
                    <?php $form->showLabel('Actual Budget'); ?>
                    <?php
                    if ($object_id > 0) {
                        echo $w2Pconfig['currency_symbol'] . '&nbsp;' . formatCurrency($object->project_actual_budget, $AppUI->getPref('CURRENCYFORM'));
                    } else {
                        echo $AppUI->_('Dynamically calculated');
                    }
                    ?>
                </p>
            <?php } ?>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Priority'); ?>
                <?php $form->showField('project_priority', (int) $object->project_priority, array(), $projectPriority); ?>
            </p>
            <p>
                <?php $form->showLabel('Short Name'); ?>
                <?php $form->showField('project_short_name', $object->project_short_name, array('maxlength' => 10)); ?>
            </p>
            <p>
                <?php $form->showLabel('Color Identifier'); ?>
                <input type="text" name="project_color_identifier" value="<?php echo ($object->project_color_identifier) ? $object->project_color_identifier : 'FFFFFF'; ?>" size="10" maxlength="6" onblur="setColor();" class="text" /> *
                <a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><?php echo $AppUI->_('change color'); ?></a>
                <a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><span id="test" style="border:solid;border-width:1;border-right-width:0;background:#<?php echo ($object->project_color_identifier) ? $object->project_color_identifier : 'FFFFFF'; ?>;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="border:solid;border-width:1;border-left-width:0;background:#FFFFFF">&nbsp;&nbsp;</span></a>
            </p>
            <p>
                <?php $form->showLabel('Project Type'); ?>
                <?php $form->showField('project_type', (int) $object->project_type, array(), $ptype); ?>
            </p>
            <p>
            <table width="100%" bgcolor="#cccccc">
                <tr>
                    <td><?php echo $AppUI->_('Status'); ?> *</td>
                    <td nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?></td>
                    <td><?php echo $AppUI->_('Active'); ?>?</td>
                </tr>
                <tr>
                    <td>
                        <?php $form->showField('project_status', $object->project_status, array(), $pstatus); ?>
                    </td>
                    <td>
                        <strong><?php echo sprintf("%.1f%%", $object->project_percent_complete); ?></strong>
                    </td>
                    <td>
                        <input type="checkbox" value="1" name="project_active" <?php echo $object->project_active || $object_id == 0 ? 'checked="checked"' : ''; ?> />
                    </td>
                </tr>
            </table>
            </p>
            <p>
                <?php $form->showLabel('Import tasks from'); ?>
                <?php
                $templates = $object->loadAll('project_name', 'project_status = ' . w2PgetConfig('template_projects_status_id'));
                $templateProjects[] = '';
                foreach($templates as $key => $data) {
                    $templateProjects[$key] = $data['project_name'];
                }
                echo arraySelect($templateProjects, 'import_tasks_from', 'size="1" class="text"', -1, false);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('project_description', $object->project_description); ?>
            </p>
            <p>
                <?php $form->showLabel('Notify by Email'); ?>
                <input type="checkbox" name="email_project_owner_box" id="email_project_owner_box" <?php echo ($tt ? 'checked="checked"' : '');?> />
                <?php echo $AppUI->_('Project Owner'); ?>
                <input type="hidden" name="email_project_owner" id="email_project_owner" value="<?php echo ($object->project_owner ? $object->project_owner : '0');?>" />
                <input type='checkbox' name='email_project_contacts_box' id='email_project_contacts_box' <?php echo ($tp ? 'checked="checked"' : ''); ?> />
                <?php echo $AppUI->_('Project Contacts'); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('project_url', $object->project_url, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Staging URL'); ?>
                <?php $form->showField('project_demo_url', $object->project_demo_url, array('maxlength' => 255)); ?>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $object->project_id, 'edit');
            echo $custom_fields->getHTML();
            ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>