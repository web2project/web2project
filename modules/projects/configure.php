<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

//config screen
//store project settings

$project = new CProject();
$properties = get_class_vars(get_class($project));

// setup the title block
$titleBlock = new CTitleBlock('Configure Projects Module', 'support.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'modules list');
$titleBlock->show();

//foreach($properties as $name => $value) {
//    echo "$name => $value".'<br />';
//}

$fields = w2p_Core_Module::getSettings('projects', 'index_list');
foreach ($fields as $field => $text) {
    $fieldList[] = $field;
    $fieldNames[] = $text;
}

echo '<pre>'; print_r($fieldList); print_r($fieldNames); echo '</pre>';