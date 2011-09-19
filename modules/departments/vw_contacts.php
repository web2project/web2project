<?php /* $Id: vw_contacts.php 1516 2010-12-05 07:18:58Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/departments/vw_contacts.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $dept_id, $dept, $company_id;
?>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">

<tr><th><?php echo $AppUI->_('Name'); ?></th><th><?php echo $AppUI->_('Email'); ?></th><th><?php echo $AppUI->_('Telephone'); ?></th></tr>
<?php
$contacts = CDepartment::getContactList($AppUI, $dept_id);

if (count($contacts) > 0) {
    $htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    foreach ($contacts as $contact_id => $contact_data) {

        echo '<tr><td><a href="./index.php?m=contacts&a=view&contact_id=' . $contact_data['contact_id'] . '">' . $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'] . '</a></td>';
        echo $htmlHelper->createColumn('contact_email', $contact_data);
        echo '<td>' . $contact_data['contact_phone'] . '</td></tr>';
    }
} else {
    ?><tr><td colspan="5"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>
	<tr>
		<td colspan="3" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class="button" value="<?php echo $AppUI->_('new contact'); ?>" onclick="javascript:window.location='./index.php?m=contacts&a=addedit&company_id=<?php echo $company_id; ?>&company_name=<?php echo $dept['company_name']; ?>&dept_id=<?php echo $dept['dept_id']; ?>&dept_name=<?php echo $dept['dept_name']; ?>'">			
		</td>
	</tr>
</table>