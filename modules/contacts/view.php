<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$object_id = (int) w2PgetParam($_GET, 'contact_id', 0);

$tab = $AppUI->processIntState('ContactVwTab', $_GET, 'tab', 0);

$object = new CContact();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

$is_user = $object->isUser($object_id);

// Get the contact details for company and department
$company_detail = $object->getCompanyDetails();
$dept_detail = $object->getDepartmentDetails();

// Get the Contact info (phone, emails, etc) for the contact
$methods = $object->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

// setup the title block
$ttl = 'View Contact';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=contacts', 'contacts list');
if ($canEdit) {
    $titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$object_id, 'edit this contact');
}
if ($object->user_id) {
    $titleBlock->addCrumb('?m=users&a=view&user_id='.$object->user_id, 'view this user');
}
if ($canDelete) {
    $titleBlock->addCrumbDelete('delete contact', $canDelete, $msg);
}
$titleBlock->show();

$view = new \Web2project\Output\HTML\View($AppUI, $object, 'Contact');
echo $view->renderDelete();

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);
