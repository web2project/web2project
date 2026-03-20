<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

global $gantt_arr, $w2Pconfig, $gtask_sliced, $showNoMilestones;

$AppUI->setMsg('PDF Export has been removed from web2project. Use Print > Save to PDF in your browser instead', UI_MSG_WARNING, true);

$AppUI->redirect('m=tasks&a=todo');