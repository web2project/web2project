<?php

$object_id = (int) w2PgetParam($_GET, 'id', 0);

$titleBlock = new w2p_Theme_TitleBlock('Email Templating', 'rdf2.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&u=templating', 'template admin');
$titleBlock->show();

$q = new w2p_Database_Query();
$q->addTable('email_templates');
$q->addWhere('email_template_id = ' . $object_id);
$template = $q->loadHash('email_template_id');

$body = $template['email_template_body'];
$body = str_replace('\n', "\n", $body);
//$body = nl2br($body);

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<script language="javascript" type="text/javascript">
    function submitIt() {
        var form = document.addedit;
        form.submit();
    }
</script>
<form name="addedit" action="?m=system&u=templating" method="post" accept-charset="utf-8" class="form-horizontal addedit email-templating">
    <input type="hidden" name="dosql" value="do_templating_aed" />
    <input type="hidden" name="email_template_id" value="<?php echo $object_id; ?>" />

    <div class="std addedit companies">
        <div class="column left">
            <p>
                <?php $form->showLabel('Template Name'); ?>
                <?php $form->showField('email_template_name', $template['email_template_name'], array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Identifier'); ?>
                <?php $form->showField('email_template_identifier', $template['email_template_identifier'], array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Language'); ?>
                <?php $form->showField('email_template_language', $template['email_template_language'], array('maxlength' => 255)); ?>
            </p>
            <?php
            $form->showCancelButton();
            ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Subject'); ?>
                <?php $form->showField('email_template_subject', $template['email_template_subject'], array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Body'); ?>
                <?php $form->showField('email_template_description', $body); ?>
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>