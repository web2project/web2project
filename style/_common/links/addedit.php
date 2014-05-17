<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit links">
    <input type="hidden" name="dosql" value="do_link_aed" />
    <input type="hidden" name="link_id" value="<?php echo $object->getId(); ?>" />
    <!-- TODO: Right now, link owner is hard coded, we should make this a select box like elsewhere. -->
    <input type="hidden" name="link_owner" value="<?php echo $object->link_owner; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit links">
        <div class="column left">
            <p>
                <?php $form->showLabel('Link Name'); ?>
                <?php $form->showField('link_name', $object->link_name, array('maxlength' => 255)); ?>
                <?php if ($object_id) { ?>
                    <a href="<?php echo $object->link_url; ?>" target="_blank"><?php echo $AppUI->_('go'); ?></a>
                <?php } ?>
            </p>
            <?php if ($link_id) { ?>
                <p>
                    <?php $form->showLabel('Created By'); ?>
                    <?php $form->showField('link_owner', $object->link_owner, array(), $users); ?>
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Category'); ?>
                <?php $form->showField('link_category', $object->link_category, array(), $types); ?>
            </p>
            <p>
                <?php $form->showLabel('Project'); ?>
                <?php $form->showField('link_project', $object->link_project, array(), $projects); ?>
            </p>
            <p>
                <?php $form->showLabel('Task'); ?>
                <input type="hidden" name="link_task" value="<?php echo $object->link_task; ?>" />
                <input type="text" class="text" name="task_name" value="<?php echo isset($object->task_name) ? $object->task_name : ''; ?>" size="40" disabled="disabled" />
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select task'); ?>..." onclick="popTask()" />
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('link_description', $object->link_description); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('link_url', $object->link_url, array()); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>