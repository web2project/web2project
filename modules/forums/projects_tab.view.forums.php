<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template

global $AppUI, $project;

$items = $project->getForumList();

$module = new w2p_System_Module();
$fields = $module->loadSettings('forums', 'projects_view');

if (0 == count($fields)) {
    $fieldList = array('forum_name', 'forum_description', 'forum_owner', 'forum_last_date');
    $fieldNames = array('Forum Name', 'Description', 'Owner', 'Last Post Info');

    $module->storeSettings('forums', 'projects_view', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}

?><a name="forums-projects_view"> </a> <?php

$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items);
echo $listTable->endTable();