<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $titleBlock, $project_id;

$canView = canView('reports');
if ($canView) {
    $titleBlock->addCrumb('?m=reports&project_id=' . $project_id, 'reports');
}