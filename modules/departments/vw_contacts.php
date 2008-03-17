<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $dept_id, $dept, $company_id;
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<?php
echo '<tr><th>' . $AppUI->_('Name') . '</th><th>' . $AppUI->_('Email') . '</th><th>' . $AppUI->_('Telephone') . '</th></tr>';

$q = new DBQuery;
$q->addTable('contacts', 'con');
$q->addQuery('contact_id, con.contact_first_name');
$q->addQuery('con.contact_last_name, contact_email, contact_phone');
$q->addWhere('contact_department = ' . (int)$dept_id);
$q->addWhere('(contact_owner = ' . (int)$AppUI->user_id . ' OR contact_private = "0")');
$q->addOrder('contact_first_name');
$contacts = $q->loadHashList('contact_id');

foreach ($contacts as $contact_id => $contact_data) {
	echo '<tr><td><a href="./index.php?m=contacts&a=view&contact_id=' . $contact_data['contact_id'] . '">' . $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'] . '</a></td>';
	echo '<td>' . $contact_data['contact_email'] . '</td>';
	echo '<td>' . $contact_data['contact_phone'] . '</td></tr>';
}
echo '
<tr><td colspan="3" align="right" valign="top" style="background-color:#ffffff">
<input type="button" class="button" value="' . $AppUI->_('new contact') . '" onclick="javascript:window.location=\'./index.php?m=contacts&a=addedit&company_id=' . $company_id . '&company_name=' . $dept['company_name'] . '&dept_id=' . $dept['dept_id'] . '&dept_name=' . $dept['dept_name'] . '\'">
</td></tr>
</table>';
?>