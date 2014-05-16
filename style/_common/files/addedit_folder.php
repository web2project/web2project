<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="folderFrm" action="?m=<?php echo $m; ?>" enctype="multipart/form-data" method="post" class="addedit files-folder">
    <input type="hidden" name="dosql" value="do_folder_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="file_folder_id" value="<?php echo $object_id; ?>" />
    <input type="hidden" name="redirect" value="<?php echo $referrer; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit departments">
        <div class="column left">
            <p>
                <?php $form->showLabel('Subfolder of'); ?>
                <?php
                $parent_folder = ($object_id > 0) ? $object->file_folder_parent : $file_folder_parent;
                echo arraySelect($folders, 'file_folder_parent', 'style="width:175px;" class="text"', $parent_folder);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Folder Name'); ?>
                <?php $form->showField('file_folder_name', $object->file_folder_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('file_folder_description', $object->file_folder_description); ?>
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>