<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

$tab = $AppUI->processIntState('CompVwTab', $_GET, 'tab', 0);

$company = new CCompany();

if (!$company->load($company_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $company->canEdit();
$canDelete = $company->canDelete();
$deletable = $canDelete;            //TODO: this should be removed once the $deletable variable is removed

$contact = new CContact();
$canCreateContacts = $contact->canCreate();

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Company', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canCreateContacts) {
    $titleBlock->addButton('New contact', '?m=contacts&a=addedit&company_id=' . $company_id);
}
if ($canEdit) {
    if ( $AppUI->isActiveModule('departments') ) {
        $titleBlock->addButton('New department', '?m=departments&a=addedit&company_id=' . $company_id);
    }
    $titleBlock->addButton('New project', '?m=projects&a=addedit&company_id=' . $company_id);

	$titleBlock->addCrumb('?m=companies&a=addedit&company_id=' . $company_id, 'edit this company');

	if ($canDelete && $deletable) {
		$titleBlock->addCrumbDelete('delete company', $deletable, $msg);
	}
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $company, 'Company');
echo $view->renderDelete();

$types = w2PgetSysVal('CompanyType');

include $AppUI->getTheme()->resolveTemplate('companies/view');

// tabbed information boxes
$moddir = W2P_BASE_DIR . '/modules/companies/';
$tabBox = new CTabBox('?m=companies&a=view&company_id=' . $company_id, '', $tab);
$tabBox->add($moddir . 'vw_projects', 'Active Projects');
$tabBox->add($moddir . 'vw_projects', 'Archived Projects');
$tabBox->show();