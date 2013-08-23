<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id;
// Forums mini-table in project view action

$items = CProject::getForums($AppUI, $project_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('forums', 'projects_view');


if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('forum_name', 'forum_description', 'forum_owner',
        'forum_last_date');
    $fieldNames = array('Forum Name', 'Description', 'Owner', 'Last Post Info');

    //$module->storeSettings('forums', 'projects_view', $fieldList, $fieldNames);
    $fields = array_combine($fieldList, $fieldNames);
}
?>
<a name="forums-projects_view"> </a>
<?php
$listTable = new w2p_Output_ListTable($AppUI);
$listTable->df .= ' ' . $AppUI->getPref('TIMEFORMAT');

echo $listTable->startTable();
echo $listTable->buildHeader($fields);
echo $listTable->buildRows($items);
echo $listTable->endTable();