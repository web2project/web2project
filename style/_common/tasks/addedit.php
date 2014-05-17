<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>&project_id=<?php echo $task_project; ?>" method="post" onSubmit="return submitIt(document.editFrm);" accept-charset="utf-8" class="addedit tasks">
    <input name="dosql" type="hidden" value="do_task_aed" />
    <input name="task_id" type="hidden" value="<?php echo $object->getId(); ?>" />
    <input name="task_project" type="hidden" value="<?php echo $task_project; ?>" />
    <input name="old_task_parent" type="hidden" value="<?php echo $object->task_parent; ?>" />
    <input name='task_contacts' id='task_contacts' type='hidden' value="<?php echo implode(',', $selected_contacts); ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit tasks">
        <div class="column left">
            <p>
                <label>&nbsp;</label>
                <span style="padding: 5px; border: outset #eeeeee 1px;background-color:#<?php echo $project->project_color_identifier; ?>; color: <?php echo bestColor($project->project_color_identifier); ?>;">
                    <strong><?php echo $AppUI->_('Project'); ?>: <?php echo $project->project_name; ?></strong>
                </span>
            </p>
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('task_name', $object->task_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Priority'); ?>
                <?php $form->showField('task_priority', (int) $object->task_priority, array(), $priority); ?>
            </p>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Status'); ?>
                <?php $form->showField('task_status', (int) $object->task_status, array(), $status); ?>
            </p>
            <p>
                <?php $form->showLabel('Progress'); ?>
                <?php echo arraySelect($percent, 'task_percent_complete', 'size="1" class="text"', $object->task_percent_complete) . '%'; ?>
            </p>
            <p>
                <?php $form->showLabel('Milestone'); ?>
                <input type="checkbox" value="1" name="task_milestone" id="task_milestone" <?php if ($object->task_milestone) { ?>checked="checked"<?php } ?> onClick="toggleMilestone()" />
            </p>
            <p><input class="button btn btn-primary save" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save'); ?>" onclick="submitIt(document.editFrm);" /></p>
        </div>
    </div>
    <div name="hiddenSubforms" id="hiddenSubforms" style="display: none;"></div>
</form>