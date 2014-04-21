<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);
$department_id = (int) w2PgetParam($_GET, 'department_id', 0);
$dept_id = max($dept_id, $department_id);

$department = new CDepartment();

if (!$department->load($dept_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $department->canEdit();
$canDelete = $department->canDelete();


$tab = $AppUI->processIntState('DeptVwTab', $_GET, 'tab', 0);

$countries = w2PgetSysVal('GlobalCountries');
$types = w2PgetSysVal('DepartmentType');

$titleBlock = new w2p_Theme_TitleBlock('View Department', 'icon.png', $m);
$titleBlock->addCrumb('?m=companies', 'company list');
$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $department->dept_company, 'view this company');
$titleBlock->addCrumb('?m=' . $m, $m . ' list');

if ($canEdit) {
    $titleBlock->addCell();
    $titleBlock->addButton('New department', '?m=departments&a=addedit&company_id=' . $department->dept_company . '&dept_parent=' . $dept_id);
    $titleBlock->addCrumb('?m=departments&a=addedit&dept_id=' . $dept_id, 'edit this department');

    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete department', $canDelete, $msg);
    }
}
$titleBlock->show();

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData((array) $department);
?>
<script language="javascript" type="text/javascript">
<?php
	// security improvement:
	// some javascript functions may not appear on client side in case of user not having write permissions
	// else users would be able to arbitrarily run 'bad' functions
	if ($canDelete) {
?>
function delIt() {
	if (confirm('<?php echo $AppUI->_('departmentDelete', UI_OUTPUT_JS); ?>')) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<form name="frmDelete" action="./index.php?m=departments" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>" />
</form>
<?php

include $AppUI->getTheme()->resolveTemplate('departments/view');

// tabbed information boxes
$tabBox = new CTabBox('?m=departments&a=' . $a . '&dept_id=' . $dept_id, '', $tab);
$tabBox->add(W2P_BASE_DIR . '/modules/departments/vw_contacts', 'Contacts');
// include auto-tabs with 'view' explicitly instead of $a, because this view is also included in the main index site
$tabBox->show();