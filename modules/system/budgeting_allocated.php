<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->getTheme()->loadCalendarJS();

$budget_id = (int) w2PgetParam($_GET, 'budget_id', 0);

if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}
$df = $AppUI->getPref('SHDATEFORMAT');

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('None specified')), $companies);

$budgetCategory = w2PgetSysVal('BudgetCategory');
$budgetCategory = arrayMerge(array('0' => $AppUI->_('None specified')), $budgetCategory);

// load the record data
$budget = new CSystem_Budget();
$budget->load($budget_id);

$titleBlock = new w2p_Theme_TitleBlock('Budget Allocated', 'myevo-weather.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->addCrumb('?m=system&a=budgeting', 'setup budgets');
$titleBlock->show();