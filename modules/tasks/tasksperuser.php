<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (isset($_POST['company_id'])) {
	$AppUI->setState('CompanyIdxFilter', $_POST['company_id']);
}
$company_id = $AppUI->getState('CompanyIdxFilter') ? $AppUI->getState('CompanyIdxFilter') : 'all';

$log_all_projects = true; // show tasks for all projects

if (!isset($user_id)) {
    $user_id = $AppUI->user_id;
}
$task = new CTask();

$obj = $task;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// get CCompany() to filter tasks by company
$comp = new CCompany();
$companies = $comp->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$compFilter = arrayMerge(array('all' => $AppUI->_('All Companies')), $companies);

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Tasks per User', 'icon.png', $m);
$titleBlock->addCell(arraySelect($compFilter, 'company_id', 'size="1" class="text" onChange="document.companyFilter.submit();"', $company_id, false), '', '<form action="?m=tasks&amp;a=tasksperuser" method="post" name="companyFilter" accept-charset="utf-8">', '</form>');
$titleBlock->addCell($AppUI->_('Company') . ':');
$titleBlock->addCrumb('?m=tasks', 'tasks list');
$titleBlock->addCrumb('?m=tasks&amp;a=todo&amp;user_id=' . $user_id, 'my todo');
$titleBlock->show();

// include the re-usable sub view
$min_view = false;
include W2P_BASE_DIR . '/modules/tasks/tasksperuser_sub.php';