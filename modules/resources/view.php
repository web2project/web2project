<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'resource_id', 0);

$object = new CResource();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

$titleBlock = new w2p_Theme_TitleBlock('View Resource', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($canEdit) {
    $titleBlock->addCrumb('?m=resources&a=addedit&resource_id=' . $object_id, 'edit this resource');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete resource', $canDelete, 'no delete permission');
    }
}
$titleBlock->show();

$view = new \Web2project\Controllers\View($AppUI, $object, 'Resource');
echo $view->renderDelete();

$types = w2PgetSysVal('ResourceTypes');
$types[0] = 'Not Specified';
$customLookups = array('resource_type' => $types);

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);
