<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit resources">
    <input type="hidden" name="dosql" value="do_resource_aed" />
    <input type="hidden" name="resource_id" value="<?php echo $object->getId(); ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit resources">
        <div class="column left">
            <p>
                <?php $form->showLabel('Resource Identifier'); ?>
                <?php $form->showField('resource_key', $object->resource_key, array('maxlength' => 64)); ?>
            </p>
            <p>
                <?php $form->showLabel('Resource Name'); ?>
                <?php $form->showField('resource_name', $object->resource_name, array('maxlength' => 255)); ?>
            </p>
            <p><?php $form->showLabel('Type'); ?>
                <?php $form->showField('resource_type', $object->resource_type, array(), $typelist); ?>
            </p>
            <p>
                <?php $form->showLabel('Max Allocation'); ?>
                <?php $form->showField('resource_max_allocation', $object->resource_max_allocation, array(), $percent); ?>
            </p>
            <p>
                <?php $form->showLabel('Notes'); ?>
                <?php $form->showField('resource_note', $object->resource_description); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>