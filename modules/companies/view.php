<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$object_id = (int) w2PgetParam($_GET, 'company_id', 0);

$tab = $AppUI->processIntState('CompVwTab', $_GET, 'tab', 0);

$object = new CCompany();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

$contact = new CContact();
$canCreateContacts = $contact->canCreate();

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Company', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canCreateContacts) {
    $titleBlock->addButton('New contact', '?m=contacts&a=addedit&company_id=' . $object_id);
}
if ($canEdit) {
    if ( $AppUI->isActiveModule('departments') ) {
        $titleBlock->addButton('New department', '?m=departments&a=addedit&company_id=' . $object_id);
    }
    $titleBlock->addButton('New project', '?m=projects&a=addedit&company_id=' . $object_id);

    $titleBlock->addCrumb('?m=companies&a=addedit&company_id=' . $object_id, 'edit this company');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete company', $deletable, $msg);
    }
}
$titleBlock->show();

$view = new \Web2project\Controllers\View($AppUI, $object, 'Company');
echo $view->renderDelete();

$types = w2PgetSysVal('CompanyType');

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);

// tabbed information boxes
$moddir = W2P_BASE_DIR . '/modules/companies/';
$tabBox = new CTabBox('?m=companies&a=view&company_id=' . $object_id, '', $tab);
$tabBox->add($moddir . 'vw_projects', 'Active Projects');
$tabBox->add($moddir . 'vw_projects', 'Archived Projects');
$tabBox->show();
