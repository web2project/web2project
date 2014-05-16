<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);
$object_id = (int) w2PgetParam($_GET, 'department_id', 0);
$object_id = max($dept_id, $object_id);

$dept_parent = (int) w2PgetParam($_GET, 'dept_parent', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

$object = new CDepartment();
$object->setId($object_id);

$canAddEdit = $object->canAddEdit();
$canAuthor = $object->canCreate();
$canEdit = $object->canEdit();

if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}


$obj = $AppUI->restoreObject();
if ($obj) {
    $object = $obj;
    $object_id = $object->getId();
} else {
    $object->load($object_id);
}
if (!$object && $object_id > 0) {
    $AppUI->setMsg('Department');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect('m=' . $m);
}

$company_id = $object->dept_id ? $object->dept_company : $company_id;

if (!$object_id && !$company_id) {
    $AppUI->setMsg('badCompany', UI_MSG_ERROR);
    $AppUI->redirect('m=companies');
}

// collect all the departments in the company
if ($company_id) {
    $company = new CCompany();
    $company->load($company_id);
    $companyName = $company->company_name;
    $depts = $object->loadOtherDepts(null, $company_id, 0);
    $depts = arrayMerge(array('0' => '- ' . $AppUI->_('Select Department') . ' -'), $depts);
}

// setup the title block
$ttl = $object_id > 0 ? 'Edit Department' : 'Add Department';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=companies', 'companies list');
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('company', $company_id);
if ($dept_id) {
    $titleBlock->addCrumb('?m=departments&a=view&dept_id=' . $dept_id, 'view this department');
}
$titleBlock->show();

// load the department types
$types = w2PgetSysVal('DepartmentType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');
$dept_parent = ($object->dept_parent) ? $object->dept_parent : $dept_parent;
?>
<script language="javascript" type="text/javascript">
function testURL( x ) {
	var test = 'document.editFrm.dept_url.value';
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( 'http://' + test, 'newwin', '' );
	}
}

function submitIt() {
	var form = document.editFrm;
	if (form.dept_name.value.length < 2) {
		alert( '<?php echo $AppUI->_('deptValidName', UI_OUTPUT_JS); ?>' );
		form.dept_name.focus();
	} else {
		form.submit();
	}
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('departments/addedit');