<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI;
$selected = w2PgetParam($_POST, 'bulk_selected_file', 0);
$redirect = w2PgetParam($_POST, 'redirect', '');
$bulk_file_project = w2PgetParam($_POST, 'bulk_file_project', 'O');
$bulk_file_folder = w2PgetParam($_POST, 'bulk_file_folder', 'O');
//print_r($_POST);die;

$perms = &$AppUI->acl();

if (is_array($selected) && count($selected)) {
	$upd_file = new CFile();
	foreach ($selected as $key => $val) {
		if ($key) {
			//If user is not permitted to edit then fail silently
			if (!$perms->checkModuleItem('files', 'edit', $key)) {
				continue;
			}
			$upd_file->load($key);
		}

		if (isset($_POST['bulk_file_project']) && $bulk_file_project != '' && $bulk_file_project != 'O') {
			if ($upd_file->file_id) {
				// move the file on filesystem if the affiliated project was changed
				if ($upd_file->file_project != $bulk_file_project) {
					$oldProject = $upd_file->file_project;
					$upd_file->file_project = $bulk_file_project;
					$res = $upd_file->moveFile($oldProject, $upd_file->file_real_filename);
					if (!$res) {
						$AppUI->setMsg('At least one File could not be moved', UI_MSG_ERROR);
					}
				}
				$upd_file->store();
			}
		}
		if (isset($_POST['bulk_file_folder']) && $bulk_file_folder != '' && $bulk_file_folder != 'O') {
			if ($upd_file->file_id) {
				$upd_file->file_folder = $bulk_file_folder;
				$upd_file->store();
			}
		}
		echo db_error();
	}
}
$AppUI->redirect($redirect);