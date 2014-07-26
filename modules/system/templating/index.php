<?php

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
$locale = w2PgetParam($_POST, 'locale', 'en_US');

$titleBlock = new w2p_Theme_TitleBlock('Email Templating', 'rdf2.png', $m);
$titleBlock->addButton('new template', '?m=system&u=templating&a=addedit');
$titleBlock->module = 'system&u=templating';
$titleBlock->addFilterCell('Language', 'locale', $langlist, $locale);
$titleBlock->module = 'system';
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$templateLoader = new CSystem_Template();
$templates = $templateLoader->loadTemplates($locale);

?><table class="tbl list modules"><?php

// todo: use our proper table generation
echo '<tr><th></th><th>Template Name</th><th>Identifier</th><th>Language</th><th>Email Subject</th><th>Email Body</th></tr>';

foreach ($templates as $template)
{
    //todo: generate the row
    echo '<tr>';
    echo '<td><a href="?m=system&u=templating&a=addedit&id=' . $template['email_template_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a></td>';
    echo '<td>' . $template['email_template_name'] . '</td>';
    echo '<td>' . $template['email_template_identifier'] . '</td>';
    echo '<td>' . $template['email_template_language'] . '</td>';
    echo '<td>' . $template['email_template_subject'] . '</td>';

    $body = $template['email_template_body'];
    $body = str_replace('\n', "\n", $body);
    $body = nl2br($body);
    echo '<td style="text-align: left">' . $body . '</td>';
    echo '</tr>';
}
?></table><?php

//todo: add english as the default for each template