<?php /* $Id: vw_contacts.php 1516 2010-12-05 07:18:58Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/departments/vw_contacts.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $dept_id, $dept, $company_id;

$contacts = CDepartment::getContactList($AppUI, $dept_id);

$fieldList = array();
$fieldNames = array();

$module = new w2p_Core_Module();
$fields = $module->loadSettings('contacts', 'department_view');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('contact_name', 'contact_job',
        'contact_email', 'contact_phone', 'dept_name');
    $fieldNames = array('Name', 'Job Title', 'Email', 'Phone',
        'Department');

    $module->storeSettings('contacts', 'department_view', $fieldList, $fieldNames);
}
?>
<a name="contacts-department_view"> </a>
<table class="tbl list">
    <tr>
        <?php
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

    foreach ($contacts as $row) {
        echo '<tr>';
        $htmlHelper->stageRowData($row);        
        foreach ($fieldList as $index => $column) {
            echo $htmlHelper->createCell($fieldList[$index], $row[$fieldList[$index]]);
        }
        echo '</tr>';
	}
} else {
	?><tr><td colspan="<?php echo count($fieldList); ?>"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>

	<tr>
		<td colspan="<?php echo count($fieldList); ?>" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class=button value="<?php echo $AppUI->_('new contact') ?>" onClick="javascript:window.location='./index.php?m=contacts&a=addedit&company_id=<?php echo $company->company_id; ?>'">
		</td>
	</tr>
</table>