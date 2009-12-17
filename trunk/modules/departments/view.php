<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $department, $min_view;
$dept_id = isset($_GET['dept_id']) ? w2PgetParam($_GET, 'dept_id', 0) : (isset($department) ? $department : 0);

$msg = '';
$department = new CDepartment();
// check permissions
$canRead = !getDenyRead($m, $dept_id);
$canEdit = !getDenyEdit($m, $dept_id);

if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}
$AppUI->savePlace();

if (isset($dept_id) && $dept_id > 0) {
	$AppUI->setState('DeptIdxDepartment', $dept_id);
}
$dept_id = $AppUI->getState('DeptIdxDepartment') !== null ? $AppUI->getState('DeptIdxDepartment') : ($AppUI->user_department > 0 ? $AppUI->user_department : $company_prefix . $AppUI->user_company);

$tab = $AppUI->processIntState('DeptVwTab', $_GET, 'tab', 0);

$countries = w2PgetSysVal('GlobalCountries');
// load the department types
$types = w2PgetSysVal('DepartmentType');

$department->loadFull($AppUI, $dept_id);

if (!$department) {
	$titleBlock = new CTitleBlock('Invalid Department ID', 'departments.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=companies', 'companies list');
	$titleBlock->show();
} elseif ($dept_id <= 0) {
	echo $AppUI->_('Please choose a Department first!');
} else {
	$company_id = $department->dept_company;
	if (!$min_view) {
		// setup the title block
		$titleBlock = new CTitleBlock('View Department', 'departments.png', $m, $m . '.' . $a);
		if ($canEdit) {
			$titleBlock->addCell();
			$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new department') . '">', '', '<form action="?m=departments&a=addedit&company_id=' . $company_id . '&dept_parent=' . $dept_id . '" method="post" accept-charset="utf-8">', '</form>');
		}
		$titleBlock->addCrumb('?m=departments', 'department list');
		$titleBlock->addCrumb('?m=companies', 'company list');
		$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
		if ($canEdit) {
			$titleBlock->addCrumb('?m=departments&a=addedit&dept_id=' . $dept_id, 'edit this department');

			if ($canDelete) {
				$titleBlock->addCrumbDelete('delete department', $canDelete, $msg);
			}
		}
		$titleBlock->show();
	}
?>
<script language="javascript">
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

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
	<tr valign="top">
		<td width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" border="0" width="100%">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
					<td class="hilite" width="100%">
						<?php if ($perms->checkModuleItem('companies', 'access', $department->dept_company)) { ?>
							<?php echo '<a href="?m=companies&a=view&company_id=' . $department->dept_company . '">' . htmlspecialchars($department->company_name, ENT_QUOTES) . '</a>'; ?>
						<?php } else { ?>
							<?php echo htmlspecialchars($department->company_name, ENT_QUOTES); ?>asdf
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $department->dept_name; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $department->contact_first_name . ' ' . $department->contact_last_name; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $types[$department->dept_type]; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
					<td class="hilite" width="100%"><?php echo w2p_email($department->dept_email); ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $department->dept_phone; ?></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
					<td class="hilite" width="100%"><?php echo $department->dept_fax; ?></td>
				</tr>
				<tr valign="top">
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
					<td class="hilite">
						<a href="http://maps.google.com/maps?q=<?php echo $department->dept_address1; ?>+<?php echo $department->dept_address2; ?>+<?php echo $department->dept_city; ?>+<?php echo $department->dept_state; ?>+<?php echo $department->dept_zip; ?>+<?php echo $department->dept_country; ?>" target="_blank">
						<img align="right" border="0" src="<?php echo w2PfindImage('googlemaps.gif'); ?>" width="55" height="22" alt="Find It on Google" /></a>
						<?php	echo $department->dept_address1 . (($department->dept_address2) ? '<br />' . $department->dept_address2 : '') . '<br />' . $department->dept_city . '&nbsp;&nbsp;' . $department->dept_state . '&nbsp;&nbsp;' . $department->dept_zip . (($department->dept_country) ? '<br />' . $countries[$department->dept_country] : '');?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?>:</td>
          <td class="hilite"><?php echo w2p_url($department->dept_url); ?></td>
				</tr>
			</table>
		</td>
		<td width="50%">
			<strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="1" cellpadding="2" border="0" width="100%">
			<tr>
				<td class="hilite" width="100%"><?php echo mb_str_replace(chr(10), '<br />', w2p_textarea($department->dept_desc)); ?>&nbsp;</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
<?php
	// tabbed information boxes
	$tabBox = new CTabBox('?m=departments&a=' . $a . '&dept_id=' . $dept_id, '', $tab);
	$tabBox->add(W2P_BASE_DIR . '/modules/departments/vw_contacts', 'Contacts');
	// include auto-tabs with 'view' explicitly instead of $a, because this view is also included in the main index site
	$tabBox->show();
}