<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$object_id = (int) w2PgetParam($_GET, 'dept_id', 0);
$department_id = (int) w2PgetParam($_GET, 'department_id', 0);
$object_id = max($object_id, $department_id);

$tab = $AppUI->processIntState('DeptVwTab', $_GET, 'tab', 0);

$object = new CDepartment();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();
$canDelete = $object->canDelete();

$titleBlock = new w2p_Theme_TitleBlock('View Department', 'icon.png', $m);
$titleBlock->addCrumb('?m=companies', 'company list');
$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $object->dept_company, 'view this company');
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canEdit) {
    $titleBlock->addCell();
    $titleBlock->addButton('New department', '?m=departments&a=addedit&company_id=' . $object->dept_company . '&dept_parent=' . $object_id);
    $titleBlock->addCrumb('?m=departments&a=addedit&dept_id=' . $object_id, 'edit this department');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete department', $canDelete, $msg);
    }
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $object, 'Department');
$view->setDoSQL('do_dept_aed');
$view->addField('dept_company', $object->dept_company);
$view->setKey('dept_id');
echo $view->renderDelete();

$types = w2PgetSysVal('DepartmentType');

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);

// tabbed information boxes
$tabBox = new CTabBox('?m=departments&a=' . $a . '&dept_id=' . $object_id, '', $tab);
$tabBox->show();
