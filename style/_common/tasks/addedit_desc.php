<?php

// @note Not sure why this is necessary as it's not in the other addedit.php templates.. maybe because this is a sub-template?
global $m;
?>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" name="detailFrm" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_task_aed" />
    <input type="hidden" name="task_id" value="<?php echo $object->getId(); ?>" />

    <div class="std addedit task-description">
        <div class="column left">
            <p>
                <?php $form->showLabel('Task Owner'); ?>
                <?php
                $owner = ($object->task_owner) ? $object->task_owner : $AppUI->user_id;
                $form->showField('task_owner', $owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Access'); ?>
                <?php echo arraySelect($task_access, 'task_access', 'class="text"', (int) $object->task_access, true); ?>
            </p>
            <p>
                <?php $form->showLabel('Task Parent'); ?>
                <select name='task_parent' class='text'>
                    <option value='<?php echo $object->task_id; ?>'><?php echo $AppUI->_('None'); ?></option>
                    <?php echo $task_parent_options; ?>
                </select>
            </p>
            <p>
                <?php $form->showLabel('Move to project'); ?>
                <?php echo arraySelect($projects, 'new_task_project', 'size="1" class="text" id="medium" onchange="submitIt(document.editFrm)"', $task_project); ?> (<?php echo $AppUI->_('and its children'); ?>)
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('task_related_url', $object->task_related_url, array('maxlength' => 255)); ?>
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
                    $amount = $object->budget[$id]['budget_amount'];
                    $totalBudget += $amount;
                    ?>
                    <p>
                        <?php $form->showLabel($AppUI->_($category)); ?>
                        <?php echo $w2Pconfig['currency_symbol']; ?> <?php $form->showField("budget_$id", $amount, array('maxlength' => 15)); ?>
                    </p>
                <?php } ?>
                <p>
                    <?php $form->showLabel('Total Target Budget'); ?>
                    <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                </p>
            <?php } ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Task Type'); ?>
                <?php
                $task_types = w2PgetSysVal('TaskType');
                $form->showField('task_type', $object->task_type, array(), $task_types); ?>
            </p>
            <?php if ($AppUI->isActiveModule('contacts') && canView('contacts')) { ?>
                <p>
                    <?php $form->showLabel('Contacts'); ?>
                    <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Select contacts...'); ?>" onclick="javascript:popContacts();" />
                </p>
            <?php } ?>
            <?php if (count($department_selection_list) > 1) { ?>
                <p>
                    <?php $form->showLabel('Department'); ?>
                    <?php echo arraySelect($department_selection_list, 'dept_ids[]', 'class="text" size="1"', $object->task_departments); ?>
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('task_description', $object->task_description); ?>
            </p>
            <p>
                <?php
                $custom_fields = new w2p_Core_CustomFields($m, $a, $object->task_id, 'edit');
                echo $custom_fields->getHTML();
                ?>
            </p>
        </div>
    </div>
</form>