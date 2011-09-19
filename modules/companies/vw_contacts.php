<?php /* $Id: vw_contacts.php 1516 2010-12-05 07:18:58Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/companies/vw_contacts.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company;

$contacts = CCompany::getContacts($AppUI, $company->company_id);
?>
<a name="contacts-company_view"> </a>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <?php
        $fieldList = array();
        $fieldNames = array();
        $fields = w2p_Core_Module::getSettings('contacts', 'company_view');
        if (count($fields) > 0) {
            foreach ($fields as $field => $text) {
                $fieldList[] = $field;
                $fieldNames[] = $text;
            }
        } else {
            // TODO: This is only in place to provide an pre-upgrade-safe 
            //   state for versions earlier than v3.0
            //   At some point at/after v4.0, this should be deprecated
            $fieldList = array('contact_first_name', 'contact_job', 
                'contact_email', 'contact_phone', 'dept_name');
            $fieldNames = array('Name', 'Job Title', 'Email', 'Phone', 
                'Department');
        }
//TODO: The link below is commented out because this view doesn't support sorting... yet.
        foreach ($fieldNames as $index => $name) {
            ?><th nowrap="nowrap">
<!--                <a href="?m=companies&a=view&company_id=<?php echo $company_id; ?>&sort=<?php echo $fieldList[$index]; ?>#contacts-company_view" class="hdr">-->
                    <?php echo $AppUI->_($fieldNames[$index]); ?>
<!--                </a>-->
            </th><?php
        }
        ?>
    </tr>

<?php
if (count($contacts) > 0) {
	$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
    foreach ($contacts as $contact_id => $contact_data) {
        echo '<tr><td class="hilite">';
		echo '<a href="./index.php?m=contacts&a=view&contact_id=' . $contact_data['contact_id'] . '">'; 
		echo $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'];
		echo '</a>';
		echo '</td>';
		echo '<td class="hilite">' . $contact_data['contact_job'] . '</td>';
        echo $htmlHelper->createColumn('contact_email', $contact_data);
		echo '<td class="hilite">' . $contact_data['contact_phone'] . '</td>';
		echo '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
		echo '</tr>';
	}
} else {
	?><tr><td colspan="5"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>

	<tr>
		<td colspan="5" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class=button value="<?php echo $AppUI->_('new contact') ?>" onClick="javascript:window.location='./index.php?m=contacts&a=addedit&company_id=<?php echo $company->company_id; ?>'">
		</td>
	</tr>
</table>