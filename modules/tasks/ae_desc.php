<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $task_id, $task, $users, $task_access, $department_selection_list;
global $task_parent_options, $w2Pconfig, $projects, $task_project, $can_edit_time_information, $tab;

$task_access = array(CTask::ACCESS_PUBLIC => 'Public', CTask::ACCESS_PROTECTED => 'Protected', CTask::ACCESS_PARTICIPANT => 'Participant', CTask::ACCESS_PRIVATE => 'Private');

/*
 * TODO: when we have an error and bouce back to this screen for the flash
 *   message, the arrays - task_access and others - are not being reset to
 *   good/safe values. I'm not sure of the best approach at the moment.
 *   ~ caseydk - 25 Nov 2011
 */
$perms = &$AppUI->acl();
?>
<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" name="detailFrm" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_task_aed" />
    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />

    <div class="std addedit task-description">
        <div class="column left">
            <p>
                <label><?php echo $AppUI->_('Task Owner'); ?></label>
                <?php echo arraySelect($users, 'task_owner', 'class="text"', !isset($task->task_owner) ? $AppUI->user_id : $task->task_owner); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Access'); ?></label>
                <?php echo arraySelect($task_access, 'task_access', 'class="text"', (int) $task->task_access, true); ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Task Parent'); ?>:</label>
                <select name='task_parent' class='text'>
                    <option value='<?php echo $task->task_id; ?>'><?php echo $AppUI->_('None'); ?></option>
                    <?php echo $task_parent_options; ?>
                </select>
            </p>
            <p>
                <label><?php echo $AppUI->_('Move to project'); ?>:</label>
                <?php echo arraySelect($projects, 'new_task_project', 'size="1" class="text" id="medium" onchange="submitIt(document.editFrm)"', $task_project); ?> (<?php echo $AppUI->_('and its children'); ?>)
            </p>
            <p>
                <label><?php echo $AppUI->_('Web Address'); ?></label>
                <input type="text" class="text" name="task_related_url" value="<?php echo $task->task_related_url; ?>" size="40" maxlength="255" />
            </p>
            <?php if (w2PgetConfig('budget_info_display', false)) { ?>
            <p>
                <label><?php echo $AppUI->_('Target Budgets'); ?>:</label>
                &nbsp;
            </p>
            <?php
            $billingCategory = w2PgetSysVal('BudgetCategory');
            $totalBudget = 0;
            foreach ($billingCategory as $id => $category) {
                $amount = $task->budget[$id]['budget_amount'];
                $totalBudget += $amount;
                ?>
                <p>
                    <label><?php echo $AppUI->_($category); ?>:</label>
                    <?php echo $w2Pconfig['currency_symbol'] ?> <input name="budget_<?php echo $id; ?>" id="budget_<?php echo $id; ?>" type="text" value="<?php echo $amount; ?>" class="text" />
                </p>
            <?php } ?>
            <p>
                <label><?php echo $AppUI->_('Total Target Budget'); ?>:</label>
                <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
            </p>
            <?php } ?>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Task Type'); ?></label>
                <?php
                $task_types = w2PgetSysVal('TaskType');
                echo arraySelect($task_types, 'task_type', 'class="text"', $task->task_type, true);
                ?>
            </p>
            <?php if ($AppUI->isActiveModule('contacts') && canView('contacts')) { ?>
            <p>
                <label><?php echo $AppUI->_('Contacts'); ?>:</label>
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Select contacts...'); ?>" onclick="javascript:popContacts();" />
            </p>
            <?php } ?>
            <?php if (count($department_selection_list) > 1) { ?>
            <p>
                <label><?php echo $AppUI->_('Department'); ?>:</label>
                <?php echo arraySelect($department_selection_list, 'dept_ids[]', 'class="text" size="1"', $task->task_departments); ?>
            </p>
            <?php } ?>
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <textarea name="task_description" class="textarea" cols="60" rows="10"><?php echo $task->task_description; ?></textarea>
            </p>
            <p>
                <?php
                $custom_fields = new w2p_Core_CustomFields($m, $a, $task->task_id, 'edit');
                echo $custom_fields->getHTML();
                ?>
            </p>
        </div>
    </div>
</form>
<script language="javascript" type="text/javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.detailFrm, checkDetail, saveDetail));
</script>
