<?php

$titleBlock = new w2p_Theme_TitleBlock('Email Templating', 'rdf2.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

$templateLoader = new CSystem_Template();
$templates = $templateLoader->loadAll('email_template_language');

?><table class="tbl list modules"><?php

// todo: apply translations to header
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

//todo: get a dropdown of the languages available
//todo: provide a way to view templates for a specific language
//todo: filter the templates by the language selected
//todo: add english as the default for each template