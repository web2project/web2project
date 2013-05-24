<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);



$department = new CDepartment();
$department->dept_id = $dept_id;

$canEdit   = $department->canEdit();
$canRead   = $department->canView();
$canCreate = $department->canCreate();
$canAccess = $department->canAccess();
$canDelete = $department->canDelete();

if (!$canAccess || !$canRead) {
	$AppUI->redirect(ACCESS_DENIED);
}

$department->loadFull(null, $dept_id);
if (!$department) {
	$AppUI->setMsg('Department');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

$tab = $AppUI->processIntState('DeptVwTab', $_GET, 'tab', 0);





$countries = w2PgetSysVal('GlobalCountries');
$types = w2PgetSysVal('DepartmentType');

$titleBlock = new w2p_Theme_TitleBlock('View Department', 'departments.png', $m, $m . '.' . $a);
if ($canEdit) {
    $titleBlock->addCell();
    $titleBlock->addCell('<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $AppUI->_('new department') . '">', '', '<form action="?m=departments&a=addedit&company_id=' . $department->dept_company . '&dept_parent=' . $dept_id . '" method="post" accept-charset="utf-8">', '</form>');
}

$titleBlock->addCrumb('?m=companies', 'company list');
$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $department->dept_company, 'view this company');
$titleBlock->addCrumb('?m=departments', 'department list');
if ($canEdit) {
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

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std view">
    <tr>
        <th colspan="2"><?php echo $department->dept_name; ?></th>
    </tr>
    <tr valign="top">
		<td width="50%">
			<strong><?php echo $AppUI->_('Details'); ?></strong>
			<table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
					<td class="hilite" width="100%">
						<?php if ($perms->checkModuleItem('companies', 'access', $department->dept_company)) { ?>
							<?php echo '<a href="?m=companies&a=view&company_id=' . $department->dept_company . '">' . htmlspecialchars($department->company_name, ENT_QUOTES) . '</a>'; ?>
						<?php } else { ?>
							<?php echo htmlspecialchars($department->company_name, ENT_QUOTES); ?>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('contact_name', $department->contact_name); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_type', $types[$department->dept_type]); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_email', $department->dept_email); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_phone', $department->dept_phone); ?>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
                    <?php echo $htmlHelper->createCell('dept_fax', $department->dept_fax); ?>
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
                    <?php echo $htmlHelper->createCell('dept_url', $department->dept_url); ?>
				</tr>
			</table>
		</td>
		<td width="50%">
			<strong><?php echo $AppUI->_('Description'); ?></strong>
			<table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
			<tr>
                <?php echo $htmlHelper->createCell('dept_desc', $department->dept_desc); ?>
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