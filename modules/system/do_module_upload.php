<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

if (isset($_FILES['module_upload'])) {
    $upload = $_FILES['module_upload'];

    $module = new w2p_Core_Module();

    $result = $module->deploy($upload);
    if (is_array($result)) {
        $AppUI->setMsg($result, UI_MSG_ERROR, true);
    } else {
        $AppUI->setMsg($AppUI->_('This module was expanded successfully.'), UI_MSG_OK, true);
    }
}

$AppUI->redirect('m=system&a=viewmods');
