<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit links">
    <input type="hidden" name="dosql" value="do_link_aed" />
    <input type="hidden" name="link_id" value="<?php echo $link_id; ?>" />
    <!-- TODO: Right now, link owner is hard coded, we should make this a select box like elsewhere. -->
    <input type="hidden" name="link_owner" value="<?php echo $link->link_owner; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit links">
        <div class="column left">
            <p>
                <?php $form->showLabel('Link Name'); ?>
                <?php $form->showField('link_name', $link->link_name, array('maxlength' => 255)); ?>
                <?php if ($link_id) { ?>
                    <a href="<?php echo $link->link_url; ?>" target="_blank"><?php echo $AppUI->_('go'); ?></a>
                <?php } ?>
            </p>
            <?php if ($link_id) { ?>
                <p>
                    <?php $form->showLabel('Created By'); ?>
                    <?php $form->showField('link_owner', $link->link_owner, array(), $users); ?>
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Category'); ?>
                <?php $form->showField('link_category', $link->link_category, array(), $types); ?>
            </p>
            <p>
                <?php $form->showLabel('Project'); ?>
                <?php $form->showField('link_project', $link->link_project, array(), $projects); ?>
            </p>
            <p>
                <?php $form->showLabel('Task'); ?>
                <input type="hidden" name="link_task" value="<?php echo $link->link_task; ?>" />
                <input type="text" class="text" name="task_name" value="<?php echo isset($link->task_name) ? $link->task_name : ''; ?>" size="40" disabled="disabled" />
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select task'); ?>..." onclick="popTask()" />
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('link_description', $link->link_description); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('link_url', $link->link_url, array()); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>