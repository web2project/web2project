<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

// check permissions for this record
$perms = &$AppUI->acl();

$canRead = $perms->checkModuleItem($m, 'view', $company_id);
if (!$canRead) {
  $AppUI->redirect('m=public&a=access_denied');
}

$canAdd = $perms->checkModuleItem($m, 'add');
$canEdit = $perms->checkModuleItem($m, 'edit', $company_id);
$canDelete = $perms->checkModuleItem($m, 'delete', $company_id);

$tab = $AppUI->processState('CompVwTab', $_GET, 'tab', 0);

$company = new CCompany();
$company->loadFull($AppUI, $company_id);

// check if this record has dependencies to prevent deletion
$msg = '';
$deletable = $company->canDelete($msg, $company_id);

// load the record data

if (!$company) {
	$AppUI->setMsg('Company');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// setup the title block
$titleBlock = new CTitleBlock('View Company', 'handshake.png', $m, "$m.$a");
$titleBlock->addCell();
if ($canAdd) {
  $titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new company') . '" />', '', '<form action="?m=companies&a=addedit" method="post" accept-charset="utf-8">', '</form>');	
}
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new department') . '" />', '', '<form action="?m=departments&a=addedit&company_id=' . $company_id . '" method="post" accept-charset="utf-8">', '</form>');
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
?>
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete && $deletable) {
?>
  <script language="javascript">
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

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
	<tr>
		<td valign="top" width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" width="100%">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $company->company_name; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $company->contact_first_name . ' ' . $company->contact_last_name; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $company->company_email; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
					<td class="hilite"><?php echo $company->company_phone1; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>2:</td>
					<td class="hilite"><?php echo $company->company_phone2; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
					<td class="hilite"><?php echo $company->company_fax; ?></td>
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
					<td class="hilite">
						<a href="http://<?php echo $company->company_primary_url; ?>" target="Company"><?php echo $company->company_primary_url; ?></a>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
					<td class="hilite"><?php echo $AppUI->_($types[$company->company_type]); ?></td>
				</tr>
			</table>
		</td>
		<td width="50%" valign="top">
			<strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="0" cellpadding="2" border="0" width="100%">
				<tr>
					<td class="hilite">
						<?php echo mb_str_replace(chr(10), '<br />', $company->company_description); ?>&nbsp;
					</td>
				</tr>		
			</table>
			<?php
				require_once ($AppUI->getSystemClass('CustomFields'));
				$custom_fields = new CustomFields($m, $a, $company->company_id, 'view');
				$custom_fields->printHTML();
			?>
		</td>
	</tr>
</table>

<?php
// tabbed information boxes
$moddir = W2P_BASE_DIR . '/modules/companies/';
$tabBox = new CTabBox('?m=companies&a=view&company_id=' . $company_id, '', $tab);
$tabBox->add($moddir . 'vw_active', 'Active Projects');
$tabBox->add($moddir . 'vw_archived', 'Archived Projects');
$tabBox->add($moddir . 'vw_depts', 'Departments');
$tabBox->add($moddir . 'vw_users', 'Users');
$tabBox->add($moddir . 'vw_contacts', 'Contacts');
$tabBox->show();