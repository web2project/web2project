<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $department, $min_view;
$dept_id = isset($_GET['dept_id']) ? w2PgetParam($_GET, 'dept_id', 0) : (isset($department) ? $department : 0);
$department = $dept_id;

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

if (isset($_GET['tab'])) {
	$AppUI->setState('DeptVwTab', w2PgetParam($_GET, 'tab', null));
}
$tab = $AppUI->getState('DeptVwTab') !== null ? $AppUI->getState('DeptVwTab') : 0;

$countries = w2PgetSysVal('GlobalCountries');
// load the department types
$types = w2PgetSysVal('DepartmentType');

if ($dept_id > 0) {
	// pull data
	$q = new DBQuery;
	$q->addTable('companies', 'com');
	$q->addTable('departments', 'dep');
	$q->addQuery('dep.*, company_name');
	$q->addQuery('con.contact_first_name');
	$q->addQuery('con.contact_last_name');
	$q->addJoin('users', 'u', 'u.user_id = dep.dept_owner');
	$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
	$q->addWhere('dep.dept_id = ' . (int)$dept_id);
	$q->addWhere('dep.dept_company = company_id');
	$dept = $q->loadHash();
	$q->clear();
}
if (!$dept) {
	$titleBlock = new CTitleBlock('Invalid Department ID', 'departments.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=companies', 'companies list');
	$titleBlock->show();
} elseif ($dept_id <= 0) {
	echo $AppUI->_('Please choose a Department first!');
} else {
	$company_id = $dept['dept_company'];
	if (!$min_view) {
		// setup the title block
		$titleBlock = new CTitleBlock('View Department', 'departments.png', $m, $m . '.' . $a);
		if ($canEdit) {
			$titleBlock->addCell();
			$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new department') . '">', '', '<form action="?m=departments&a=addedit&company_id=' . $company_id . '&dept_parent=' . $dept_id . '" method="post">', '</form>');
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

<form name="frmDelete" action="./index.php?m=departments" method="post">
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
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['company_name']; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['dept_name']; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['contact_first_name'] . ' ' . $dept['contact_last_name']; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $types[$dept['dept_type']]; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['dept_email']; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['dept_phone']; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept['dept_fax']; ?></td>
		</tr>
		<tr valign="top">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
			<td bgcolor="#ffffff">
			<a href="http://maps.google.com/maps?q=<?php echo $dept['dept_address1']; ?>+<?php echo $dept['dept_address2']; ?>+<?php echo $dept['dept_city']; ?>+<?php echo $dept['dept_state']; ?>+<?php echo $dept['dept_zip']; ?>+<?php echo $dept['dept_country']; ?>" target="_blank">
			<img align="right" border="0" src="<?php echo w2PfindImage('googlemaps.gif'); ?>" width="55" height="22" alt="Find It on Google" /></a>
<?php		echo @$dept['dept_address1'] . (($dept['dept_address2']) ? '<br />' . $dept['dept_address2'] : '') . '<br />' . $dept['dept_city'] . '&nbsp;&nbsp;' . $dept['dept_state'] . '&nbsp;&nbsp;' . $dept['dept_zip'] . (($dept['dept_country']) ? '<br />' . $countries[$dept['dept_country']] : '');?>
		</td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<strong><?php echo $AppUI->_('Description'); ?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff" width="100%"><?php echo str_replace(chr(10), '<br />', $dept['dept_desc']); ?>&nbsp;</td>
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
?>