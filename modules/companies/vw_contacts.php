<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company_id, $obj;

// assemble the sql statement
require_once $AppUI->getModuleClass('contacts');
require_once $AppUI->getModuleClass('departments');
$department = &new CDepartment;
$allowedDepartments = $department->getAllowedSQL($AppUI->user_id);
$q = new DBQuery;
$q->addQuery('a.*');
$q->addQuery('dept_name');
$q->addTable('contacts', 'a');
$q->leftJoin('companies', 'b', 'a.contact_company = b.company_id');
$q->leftJoin('departments', '', 'contact_department = dept_id');
$q->addWhere('contact_company = ' . (int)$obj->company_id);
$q->addWhere('
	(contact_private=0
		OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
		OR contact_owner IS NULL OR contact_owner = 0
	)');
if (count($allowedDepartments)) {
	$dpt_where = implode(' AND ', $allowedDepartments);
	$q->addWhere('( (' . $dpt_where . ') OR contact_department = 0 )');
}
$q->addOrder('contact_first_name');
$q->addOrder('contact_last_name');

$s = '';
if (!($rows = $q->loadList())) {
	echo '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">';
	echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg();
} else {
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_('Name'); ?></td>
	<th><?php echo $AppUI->_('e-mail'); ?></td>
	<th><?php echo $AppUI->_('Phone'); ?></td>
	<th><?php echo $AppUI->_('Department'); ?></td>
</tr>
<?php
	foreach ($rows as $row) {
		$contact = &new CContact;
		$contact->bind($row);
		$dept_detail = $contact->getDepartmentDetails();

		$s .= '<tr><td>';
		$s .= '<a href="./index.php?m=contacts&a=view&contact_id=' . $row['contact_id'] . '">' . $row['contact_first_name'] . ' ' . $row['contact_last_name'] . '</a>';
		$s .= '<td><a href="mailto:' . $row['contact_email'] . '">' . $row['contact_email'] . '</a></td>';
		$s .= '<td>' . $row['contact_phone'] . '</td>';
		$s .= '<td>' . $dept_detail['dept_name'] . '</td>';
		$s .= '</tr>';
	}
}

$s .= '<tr><td colspan="4" align="right" valign="top" style="background-color:#ffffff">';
$s .= '<input type="button" class=button value="' . $AppUI->_('new contact') . '" onClick="javascript:window.location=\'./index.php?m=contacts&a=addedit&company_id=' . $company_id . '&company_name=' . $obj->company_name . '\'">';
$s .= '</td></tr>';
$s .= '</table>';
echo $s;
?>