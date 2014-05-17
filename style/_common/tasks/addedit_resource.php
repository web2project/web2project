<form action="?m=tasks&a=addedit&task_project=<?php echo $task_project; ?>" method="post" name="resourceFrm" accept-charset="utf-8">
    <input type="hidden" name="task_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="dosql" value="do_task_aed" />
    <input name="hperc_assign" type="hidden" value="<?php echo $initPercAsignment; ?>"/>
    <input type="hidden" name="hassign" />

    <div class="std addedit tasks-resources">
        <div class="column left">
            <table cellspacing="0" cellpadding="2" border="0" class="well">
                <tr>
                    <td><?php echo $AppUI->_('Human Resources'); ?>:</td>
                    <td><?php echo $AppUI->_('Assigned to Task'); ?>:</td>
                </tr>
                <tr>
                    <td>
                        <?php echo arraySelect($users, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                    </td>
                    <td>
                        <?php echo arraySelect($assigned, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <table>
                            <tr>
                                <td align="right"><input type="button" class="button btn btn-primary btn-mini" value="&gt;" onclick="addUser(document.resourceFrm)" /></td>
                                <td>
                                    <select name="percentage_assignment" class="text">
                                        <?php
                                        for ($i = 5; $i <= 100; $i += 5) {
                                            echo '<option ' . (($i == 100) ? 'selected="true"' : '') . ' value="' . $i . '">' . $i . '%</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td align="left"><input type="button" class="button btn btn-primary btn-mini" value="&lt;" onclick="removeUser(document.resourceFrm)" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="column right well" style="width: 45%">
            <p>
                <?php $form->showLabel('Additional Email Comments'); ?>
                <textarea name="email_comment" class="textarea" cols="60" rows="10"></textarea>
            </p>
            <p>
                <?php $form->showLabel('notifyChange'); ?>
                <input type="checkbox" name="task_notify" id="task_notify" value="1" <?php if ($object->task_notify != '0') echo 'checked="checked"' ?> />
            </p>
            <p>
                <?php $form->showLabel('Allow users to add task logs for others'); ?>
                <input type="checkbox" value="1" name="task_allow_other_user_tasklogs" <?php echo $object->task_allow_other_user_tasklogs ? 'checked="checked"' : ''; ?> />
            </p>
        </div>
    </div>
</form>