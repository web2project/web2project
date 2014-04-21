<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('ResourceTypeTab', $_GET, 'tab', 0);

$obj = new CResource();

$perms = &$AppUI->acl();
$canEdit = canEdit('resources');

$titleBlock = new w2p_Theme_TitleBlock('Resources', 'icon.png', $m);
if ($canEdit) {
    $titleBlock->addButton('new resource', '?m=resources&a=addedit');
}
$titleBlock->show();

$resource_types = w2PgetSysVal('ResourceTypes');

$tabBox = new CTabBox('?m=resources', W2P_BASE_DIR . '/modules/resources/', $tab);
if ($tabBox->isTabbed()) {
    array_unshift($resource_types, $AppUI->_('All Resources', UI_OUTPUT_RAW));
}

foreach ($resource_types as $resource_type) {
    $tabBox->add('vw_resources', $resource_type);
}
$tabBox->show();