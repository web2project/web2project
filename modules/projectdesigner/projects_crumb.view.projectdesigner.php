<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $titleBlock, $project_id;

$titleBlock->addCrumb('?m=projectdesigner&project_id=' . $project_id, 'design this project');