<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CFileFolder();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    //$AppUI->redirect('m=files&a=addedit_folder&folder='.$obj->file_folder_id);
    $AppUI->redirect();
}
if ($result) {
    $AppUI->setMsg('File Folder '.$action, UI_MSG_OK, true);
    $AppUI->redirect('m=files');
} else {
    $AppUI->redirect('m=public&a=access_denied');
}