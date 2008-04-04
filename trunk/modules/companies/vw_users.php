<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company_id;

$q = new DBQuery;
$q->addTable('users');
$q->addQuery('user_id, user_username, contact_first_name, contact_last_name');
$q->addJoin('contacts', 'c', 'users.user_contact = contact_id', 'inner');
$q->addJoin('departments', 'd', 'd.dept_id = contact_department');
$q->addWhere('contact_company = ' . (int)$company_id);
$q->addOrder('contact_last_name');
$oDpt = new CDepartment();
$aDptsAllowed = $oDpt->getAllowedRecords($AppUI->user_id, 'dept_id, dept_name');
if (count($aDptsAllowed)) {
	$q->addWhere('(dept_id IN (' . implode(',', array_keys($aDptsAllowed)) . ') OR dept_id IS NULL OR dept_id=0 OR dept_id=\'\')');
}

if (!($rows = $q->loadList())) {
	echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg();
} else {
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_('Username'); ?></td>
	<th><?php echo $AppUI->_('Name'); ?></td>
</tr>
<?php
	$s = '';
	foreach ($rows as $row) {
		$s .= '<tr><td>';
		$s .= '<a href="./index.php?m=admin&a=viewuser&user_id=' . $row['user_id'] . '">' . $row['user_username'] . '</a>';
		$s .= '<td>' . $row['contact_first_name'] . ' ' . $row['contact_last_name'] . '</td>';
		$s .= '</tr>';
	}
	echo $s;
?>
</table>
<?php } ?>