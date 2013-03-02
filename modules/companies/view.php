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

$company->loadFull(null, $company_id);
if (!$company) {
	$AppUI->setMsg('Company');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$tab = $AppUI->processIntState('CompVwTab', $_GET, 'tab', 0);


// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View Company', 'handshake.png', $m, "$m.$a");
$titleBlock->addCell();
if ($canAdd) {
    $titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new company') . '" />', '', '<form action="?m=companies&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
if ($canEdit) {
    if ( $AppUI->isActiveModule('departments') ) {
        $titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new department') . '" />', '', '<form action="?m=departments&a=addedit&company_id=' . $company_id . '" method="post" accept-charset="utf-8">', '</form>');
    }
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new project') . '" />', '', '<form action="?m=projects&a=addedit&company_id=' . $company_id . '" method="post" accept-charset="utf-8">', '</form>');
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
?>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
	<tr>
		<td valign="top" width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_name-nolink', $company->company_name); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_displayname', $company->contact_name); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_email', $company->company_email); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_phone1', $company->company_phone1); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>2:</td>
                    <?php echo $htmlHelper->createCell('company_phone2', $company->company_phone2); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_fax', $company->company_fax); ?>
				</tr>
				<tr valign="top">
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
					<td class="hilite">
					<a href="http://maps.google.com/maps?q=<?php echo $company->company_address1; ?>+<?php echo $company->company_address2; ?>+<?php echo $company->company_city; ?>+<?php echo $company->company_state; ?>+<?php echo $company->company_zip; ?>+<?php echo $company->company_country; ?>" target="_blank">
					<img align="right" border="0" src="<?php echo w2PfindImage('googlemaps.gif'); ?>" width="55" height="22" alt="Find It on Google" /></a>
					<?php
						echo $company->company_address1 . (($company->company_address2) ? '<br />' . $company->company_address2 : '') . (($company->company_city) ? '<br />' . $company->company_city : '') . (($company->company_state) ? '<br />' . $company->company_state : '') . (($company->company_zip) ? '<br />' . $company->company_zip : '') . (($company->company_country) ? '<br />' . $countries[$company->company_country] : '');?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_primary_url', $company->company_primary_url); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('company_type', $AppUI->_($types[$company->company_type])); ?>
				</tr>
			</table>
		</td>
		<td width="50%" valign="top">
			<strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="0" cellpadding="2" border="0" width="100%">
				<tr>
                    <?php echo $htmlHelper->createCell('company_description', $company->company_description); ?>
				</tr>		
			</table>
			<?php
				$custom_fields = new w2p_Core_CustomFields($m, $a, $company->company_id, 'view');
				$custom_fields->printHTML();
			?>
		</td>
	</tr>
</table>

<?php
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