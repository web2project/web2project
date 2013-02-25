<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id, $task, $users, $task_access, $department_selection_list;
global $task_parent_options, $w2Pconfig, $projects, $task_project, $can_edit_time_information, $tab;

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
    <table class="std addedit" width="100%" border="1" cellpadding="4" cellspacing="0">
        <tr>
            <td width="50%" valign='top'>
                <table border="0">
                    <tr>
                        <td>
                            <?php if ($can_edit_time_information) { ?>
                                <?php echo $AppUI->_('Task Owner'); ?><br />
                                <?php echo arraySelect($users, 'task_owner', 'class="text"', !isset($task->task_owner) ? $AppUI->user_id : $task->task_owner); ?>
                                <br />
                            <?php } ?>
                            <?php echo $AppUI->_('Access'); ?>
                            <br />
                            <?php if(is_array($task_access)) { ?>
                                <?php echo arraySelect($task_access, 'task_access', 'class="text"', (int) $task->task_access, true); ?>
                            <?php } else { ?>
                                <input name="task_access" type="hidden" value="<?php echo (int) $task->task_access; ?>" />
                            <?php } ?>
                            <br /><?php echo $AppUI->_('Web Address'); ?>
                            <br /><input type="text" class="text" name="task_related_url" value="<?php echo $task->task_related_url; ?>" size="40" maxlength="255" />
                        </td>
                        <td valign='top'>
                            <?php echo $AppUI->_('Task Type'); ?><br />
                            <?php
                                $task_types = w2PgetSysVal('TaskType');
                                echo arraySelect($task_types, 'task_type', 'class="text"', $task->task_type, true);
                            ?>
                            <br /><br />
                            <?php
                            if ($AppUI->isActiveModule('contacts') && canView('contacts')) {
                                echo '<input type="button" class="button" value="' . $AppUI->_('Select contacts...') . '" onclick="javascript:popContacts();" />';
                            }
                            // Let's check if the actual company has departments registered
                            if (count($department_selection_list) > 1) { ?><br />
                                <?php echo $AppUI->_('Department'); ?><br />
                                <?php echo arraySelect($department_selection_list, 'dept_ids[]', 'class="text" size="1"', $task->task_departments); ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $AppUI->_('Task Parent'); ?>:</td>
                        <td rowspan="4">
                            <table>
                                <tr>
                                    <td></td>
                                    <td><?php echo $AppUI->_('Target Budgets'); ?>:</td>
                                </tr>
                                <?php
                                $billingCategory = w2PgetSysVal('BudgetCategory');
                                $totalBudget = 0;
                                foreach ($billingCategory as $id => $category) {
                                    $amount = $task->budget[$id]['budget_amount'];
                                    $totalBudget += $amount;
                                    ?>
                                    <tr>
                                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_($category); ?></td>
                                        <td nowrap="nowrap" style="text-align: left; padding-left: 40px;">
                                            <?php echo $w2Pconfig['currency_symbol'] ?> <input name="budget_<?php echo $id; ?>" id="budget_<?php echo $id; ?>" type="text" value="<?php echo $amount; ?>" size="10" class="text" />
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td nowrap="nowrap"><?php echo $AppUI->_('Total Target Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
                                    <td align="right">
                                        <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                <tr>
                    <td>
                        <select name='task_parent' class='text'>
                            <option value='<?php echo $task->task_id; ?>'><?php echo $AppUI->_('None'); ?></option>
                            <?php echo $task_parent_options; ?>
                        </select>
                    </td>
<!--                    <td><?php echo $w2Pconfig['currency_symbol'] ?><input type="text" class="text" name="task_target_budget" value="<?php echo $task->task_target_budget; ?>" size="10" maxlength="10" /></td>-->
                </tr>
            <?php if ($task_id > 0) { ?>
                <tr>
                    <td>
                        <?php echo $AppUI->_('Move this task (and its children), to project'); ?>:
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo arraySelect($projects, 'new_task_project', 'size="1" class="text" id="medium" onchange="submitIt(document.editFrm)"', $task_project); ?>
                    </td>
                </tr>
            <?php } ?>
                </table>
            </td>
            <td valign="top" align="center">
                <table><tr><td align="left">
                <?php echo $AppUI->_('Description'); ?>:
                <br />
                <textarea name="task_description" class="textarea" cols="60" rows="10"><?php echo $task->task_description; ?></textarea>
                </td></tr></table><br />
                <?php
        global $m;
        $custom_fields = new w2p_Core_CustomFields($m, 'addedit', $task->task_id, 'edit');
        $custom_fields->printHTML();
        ?>
            </td>
        </tr>
    </table>
</form>
<script language="javascript" type="text/javascript">
	subForm.push(new FormDefinition(<?php echo $tab; ?>, document.detailFrm, checkDetail, saveDetail));
</script>
