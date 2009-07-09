<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;
require_once ($AppUI->getModuleClass('files'));

$showProject = false;
require (w2PgetConfig('root_dir') . '/modules/files/index_table.php');