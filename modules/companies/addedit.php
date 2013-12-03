<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

$company = new CCompany();
$company->company_id = $company_id;

$obj = $company;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $company = $obj;
    $company_id = $company->company_id;
} else {
    $company->loadFull(null, $company_id);
}
if (!$company && $company_id > 0) {
	$AppUI->setMsg('Company');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

// setup the title block
$ttl = $company_id > 0 ? 'Edit Company' : 'Add Company';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=companies', 'companies list');
if ($company_id != 0) {
	$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
}
$titleBlock->show();


// load the company types
$types = w2PgetSysVal('CompanyType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');
?>

<script language="javascript" type="text/javascript">
function submitIt() {
	var form = document.changeclient;
	if (form.company_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('companyValidName', UI_OUTPUT_JS); ?>" );
		form.company_name.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = 'document.changeclient.company_primary_url.value';
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( 'http://' + test, 'newwin', '' );
	}
}
</script>
<?php

include $AppUI->getTheme()->resolveTemplate('companies/addedit');