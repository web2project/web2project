<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('ResourceTypeTab', $_GET, 'tab', 0);

$obj = new CResource();

$perms = &$AppUI->acl();
$canEdit = canEdit('resources');

$titleBlock = new w2p_Theme_TitleBlock('Resources', 'resources.png', $m, $m . '.' . $a);
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new resource') . '">', '', '<form action="?m=resources&a=addedit" method="post" accept-charset="utf-8">', '</form>');
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