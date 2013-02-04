<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$project_id = (int) w2PgetParam($_POST, 'project_id', 0);
$url = 'm=projectdesigner&project_id=' . $project_id;

$controller = new w2p_Controllers_Base(
                    new CProjectDesigner(), false, 'Your workspace has been ', $url, $url
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);