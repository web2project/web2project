<?php

$object_id = (int) w2PgetParam($_GET, 'id', 0);

$titleBlock = new w2p_Theme_TitleBlock('Email Templating', 'rdf2.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&u=templating', 'template admin');
$titleBlock->show();

$template = new CSystem_Template();
$template->load($object_id);

$body = $template->email_template_body;
$body = str_replace('\n', "\n", $body);

// read the installed languages
$LANGUAGES = $AppUI->loadLanguages();
$langlist = array();
foreach ($LANGUAGES as $lang => $langinfo) {
    $langlist[$lang] = $langinfo[1];
}
/*
 * NOTE: While it may seem egocentric to force US English as the default language, without this line, the
 *   language defaults to whatever is first in the dropdown.. which is Czech at the time of this writing.
 *   Since English is more widespread, I don't feel bad. ~ caseysoftware/caseydk 16 June 2014
 */
$template->email_template_language = ('' == $template->email_template_language || 'en' == $template->email_template_language) ?
    'en_US' : $template->email_template_language;


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
                <?php $form->showField('email_template_name', $template->email_template_name, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Identifier'); ?>
                <?php $form->showField('email_template_identifier', $template->email_template_identifier, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Language'); ?>
                <?php echo arraySelect($langlist, 'email_template_language', 'class=text size=1', $template->email_template_language, true); ?>
            </p>
            <?php
            $form->showCancelButton();
            ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Subject'); ?>
                <?php $form->showField('email_template_subject', $template->email_template_subject, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Body'); ?>
                <?php $form->showField('email_template_description', $body); ?>
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>