<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;

//Lets store the panels view options of the user:
$pdo = new CProjectDesigner();
$pdo->bind($_POST);
$pdo->store();

$project_id = (int) w2PgetParam($_POST, 'project_id', 0);

$AppUI->setMsg('Your workspace has been saved', UI_MSG_OK);
$AppUI->redirect('m=projectdesigner&project_id=' . $project_id);