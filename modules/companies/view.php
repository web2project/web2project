<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_GET, 'company_id', 0);



$company = new CCompany();
$company->company_id = $company_id;

$canEdit   = $company->canEdit();
$canRead   = $company->canView();
$canAdd    = $company->canCreate();
$canAccess = $company->canAccess();
$canDelete = $company->canDelete();
$deletable = $canDelete;            //TODO: this should be removed once the $deletable variable is removed
if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$contact = new CContact();
$canCreateContacts = $contact->canCreate();

$company->loadFull(null, $company_id);
if (!$company) {
	$AppUI->setMsg('Company');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$tab = $AppUI->processIntState('CompVwTab', $_GET, 'tab', 0);


// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Company', 'icon.png', $m, "$m.$a");
$titleBlock->addCell();
if ($canCreateContacts) {
    $titleBlock->addButton('New contact', '?m=contacts&a=addedit&company_id=' . $company_id);
}
if ($canEdit) {
    if ( $AppUI->isActiveModule('departments') ) {
        $titleBlock->addButton('New department', '?m=departments&a=addedit&company_id=' . $company_id);
    }
    $titleBlock->addButton('New project', '?m=projects&a=addedit&company_id=' . $company_id);
}
$titleBlock->addCrumb('?m=companies', 'company list');
if ($canEdit) {
	$titleBlock->addCrumb('?m=companies&a=addedit&company_id=' . $company_id, 'edit this company');

	if ($canDelete && $deletable) {
		$titleBlock->addCrumbDelete('delete company', $deletable, $msg);
	}
}
$titleBlock->show();
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
$htmlHelper->stageRowData(get_object_vars($company));
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
  <script language="javascript" type="text/javascript">
    function delIt() {
    	if (confirm( '<?php echo $AppUI->_('doDelete') . ' ' . $AppUI->_('Company') . '?'; ?>' )) {
    		document.frmDelete.submit();
    	}
    }
  </script>

	<form name="frmDelete" action="./index.php?m=companies" method="post" accept-charset="utf-8">
		<input type="hidden" name="dosql" value="do_company_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	</form>
<?php } ?>

<?php
// load the list of project statii and company types
$pstatus = w2PgetSysVal('ProjectStatus');
$types = w2PgetSysVal('CompanyType');
$countries = w2PgetSysVal('GlobalCountries');

include $AppUI->getTheme()->resolveTemplate('companies/view');

// tabbed information boxes
$moddir = W2P_BASE_DIR . '/modules/companies/';
$tabBox = new CTabBox('?m=companies&a=view&company_id=' . $company_id, '', $tab);
$tabBox->add($moddir . 'vw_projects', 'Active Projects');
$tabBox->add($moddir . 'vw_projects', 'Archived Projects');
if ($AppUI->isActiveModule('departments') && canView('departments')) {
    $tabBox->add($moddir . 'vw_depts', 'Departments');
}
$tabBox->add($moddir . 'vw_users', 'Users');
$tabBox->add($moddir . 'vw_contacts', 'Contacts');
$tabBox->show();