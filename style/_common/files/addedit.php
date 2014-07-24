<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" enctype="multipart/form-data" method="post" class="addedit files">
    <input type="hidden" name="dosql" value="do_file_aed" />
    <input type="hidden" name="cancel" value="0" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="file_id" value="<?php echo $object->getId(); ?>" />
    <input type="hidden" name="file_parent" value="<?php echo ($object->file_parent) ? $object->file_parent : $file_parent; ?>" />
    <input type="hidden" name="file_version_id" value="<?php echo $object->file_version_id; ?>" />
    <input type="hidden" name="redirect" value="<?php echo $referrer; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit files">
        <div class="column left" style="width: 80%">
            <p>
                <?php $form->showLabel('Folder'); ?>
                <?php if ($object_id == 0 && !$ci) { ?>
                    <?php echo arraySelect($folders, 'file_folder', 'class="text"', $folder); ?>
                <?php } else { ?>
                    <?php echo arraySelect($folders, 'file_folder', 'class="text"', $object->file_folder); ?>
                <?php } ?>
            </p>
            <?php if ($object->file_id) { ?>
                <p>
                    <?php $form->showLabel('File Name'); ?>
                    <?php echo strlen($object->file_name) == 0 ? 'n/a' : $object->file_name; ?>
                </p>
                <p>
                    <?php $form->showLabel('Type'); ?>
                    <?php echo $object->file_type; ?>
                </p>
                <p>
                    <?php $form->showLabel('Size'); ?>
                    <?php echo $object->file_size; ?> <?php echo $AppUI->_('bytes'); ?>
                </p>
                <p>
                    <?php $form->showLabel('Uploaded By'); ?>
                    <?php echo $object->file_owner; ?>
                    <!-- @TODO lookup this value -->
                </p>
            <?php } ?>
            <?php echo file_show_attr($AppUI, $form); ?>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('file_description', $object->file_description); ?>
            </p>
            <p>
                <?php $form->showLabel('Upload File'); ?>
                <input type="File" name="formfile" style="width:270px" />
            </p>
            <?php if ($ci || ($canAdmin && $object->file_checkout == 'final')) { ?>
                <p>
                    <?php $form->showLabel('Final Version'); ?>
                    <input type="checkbox" name="final_ci" id="final_ci" onclick="finalCI()" />
                </p>
            <?php } ?>
            <p>
                <?php $form->showLabel('Notify Assignees of Task or Project Owner by Email'); ?>
                <input type="checkbox" name="notify" id="notify" checked="checked" />
            </p>
            <?php if ($object->file_id && $object->file_checkout <> '' && ((int) $object->file_checkout == $AppUI->user_id || $canAdmin)) { ?>
                <p>
                    <?php $form->showLabel('&nbsp;'); ?>
                    <input type="button" class="button btn btn-danger btn-mini" value="<?php echo $AppUI->_('cancel checkout'); ?>" onclick="cancelIt()" />
                </p>
            <?php } ?>
            <p>
                <?php $form->showCancelButton(); ?>
                <?php
                if (is_writable(W2P_BASE_DIR.'/files')) {
                    $form->showSaveButton();
                } else {
                    ?><span class="error">File uploads not allowed. Please check permissions on the /files directory.</span><?php
                }
                ?>
            </p>
        </div>
    </div>
</form>