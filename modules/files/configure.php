<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Configure Files Module', 'icon.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'modules list');
$titleBlock->show();

$query = new w2p_Database_Query();
$query->addQuery('module_config_name, module_config_value');
$query->addTable('module_config');
$query->addWhere("module_name = 'files'");
$values = $query->loadList(-1, 'module_config_name');

foreach($values as $key => $array) {
    $values[$key] = $array['module_config_value'];
}

$filesystem  = w2PgetConfig('file_system', '');
$filesystems = array('' => 'default', 'amazon' => 'Amazon S3', 'dropbox' => 'Dropbox');

$form = new w2p_Output_HTML_FormHelper($AppUI);
?>
<script language="javascript" type="text/javascript">
    function submitIt()
    {
        var f = document.configureForm;
        f.submit();
    }
</script>
<form name="configureForm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="configure files">
    <input type="hidden" name="dosql" value="do_configure" />
    <div class="std configure files">
        <div class="column left">
            <p>
                <?php $form->showLabel('Default Storage System'); ?>
                <select name="file_system" id="file_system">
                    <?php foreach($filesystems as $key => $value) { ?>
                        <?php if ($key == $filesystem) { ?>
                            <option value="<?php echo $key; ?>" selected="true"><?php echo $value; ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </p>
            <p><label><?php echo $AppUI->_('Amazon S3 Settings'); ?>*</label><br /></p>
            <p>
                <?php $form->showLabel('Bucket Name'); ?>
                <?php $form->showField('aws_bucket_name', w2PgetConfig('aws_bucket_name', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Access Key ID'); ?>
                <?php $form->showField('aws_access_key', w2PgetConfig('aws_access_key', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Secret Key'); ?>
                <?php $form->showField('aws_secret_key', w2PgetConfig('aws_secret_key', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                * To use the Amazon S3 backend, you have to visit the <a href="https://console.aws.amazon.com/s3/">Developer App Console</a> to create your own bucket.
            </p>
            <p><hr /></p>
            <p><label><?php echo $AppUI->_('Dropbox Settings'); ?>**</label><br /></p>
            <p>
                <?php $form->showLabel('Key'); ?>
                <?php $form->showField('dropbox_key', w2PgetConfig('dropbox_key', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Secret'); ?>
                <?php $form->showField('dropbox_secret', w2PgetConfig('dropbox_secret', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Access Token'); ?>
                <?php $form->showField('dropbox_access_token', w2PgetConfig('dropbox_access_token', ''), array('maxlength' => 255)); ?>
            </p>
            <p>
                ** To use the Dropbox backend, you have to visit the <a href="https://www.dropbox.com/developers/apps">Developer App Console</a> to create your own application folder. You can sign up for your own <a href="https://db.tt/IRNh4mH7">Dropbox account here</a>.
            </p>
            <?php $form->showCancelButton(); ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>