<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$resource_id = (int) w2PgetParam($_GET, 'resource_id', 0);

$obj = new CResource();

if (!$obj->load($resource_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $obj->canEdit();
$canDelete = $obj->canDelete();

$titleBlock = new w2p_Theme_TitleBlock('View Resource', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($canEdit) {
    $titleBlock->addCrumb('?m=resources&a=addedit&resource_id=' . $resource_id, 'edit this resource');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete resource', $canDelete, 'no delete permission');
    }
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $obj, 'Resource');
echo $view->renderDelete();

$types = w2PgetSysVal('ResourceTypes');
$types[0] = 'Not Specified';
$customLookups = array('resource_type' => $types);

include $AppUI->getTheme()->resolveTemplate('resources/view');