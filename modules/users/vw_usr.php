<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
// @todo    remove database query

global $AppUI, $m;

$display_last_login = !((int) w2PgetParam($_REQUEST, 'tab', 0));

$fieldList = array();
$fieldNames = array();

$fields = w2p_System_Module::getSettings('users', 'index_table');

if (count($fields) > 0) {
    $fieldList = array_keys($fields);
    $fieldNames = array_values($fields);
} else {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('contact_display_name', 'user_username',
        'company_name', 'dept_name');
    $fieldNames = array('Real Name', 'Login Name', 'Company', 'Department');
//TODO: This doesn't save the columns yet as we can't allow customization yet.
}
if ($display_last_login) {
    array_unshift($fieldList,  '');
    array_unshift($fieldNames, 'Login History');
}
$fields = array_combine($fieldList, $fieldNames);

$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
$listTable->addBefore(1);
echo $listTable->buildHeader($fields, false, $m);

$types = w2PgetSysVal('UserType');
$customLookups = array('user_type' => $types);

$perms = &$AppUI->acl();

foreach ($users as $row) {
	if ($perms->isUserPermitted($row['user_id']) != $canLogin) {
		continue;
	}
    $listTable->stageRowData($row);
?>
<tr>
    <td>
        <a href="./index.php?m=users&a=addedit&user_id=<?php echo $row['user_id']; ?>"><img src="<?php echo w2PfindImage('icons/stock_edit-16.png'); ?>" alt="email" /></a>
        <a href="./index.php?m=users&a=view&tab=1&user_id=<?php echo $row['user_id']; ?>"><img src="<?php echo w2PfindImage('obj/lock.gif'); ?>" alt="email" /></a>
    </td>
	<?php if (w2PgetParam($_REQUEST, 'tab', 0) == 0) { ?>
	<td nowrap="nowrap">
	       <?php
           $user_logs = __extract_from_vw_usr($row);

		if ($user_logs) {
			foreach ($user_logs as $row_log) {
				if ($row_log['online'] == '1') {
					echo '<span style="color: green">' . $row_log['hours'] . ' ' . $AppUI->_('hrs.') . '( ' . $row_log['idle'] . ' ' . $AppUI->_('hrs.') . ' ' . $AppUI->_('idle') . ') - ' . $AppUI->_('Online');
				} else {
					echo '<span style="color: red">' . $AppUI->_('Offline');
				}
			}
		} else {
			echo '<span style="color: grey">' . $AppUI->_('Never Visited');
		}
		echo '</span>';
	} ?>
	</td>
	<td width="20%">
		<a href="mailto:<?php echo $row['contact_email']; ?>"><img src="<?php echo w2PfindImage('obj/email.gif'); ?>" alt="email" /></a>
        <?php echo $row['contact_display_name']; ?>
	</td>
    <?php
        echo $listTable->createCell('user_user', $row['user_username']);
        echo $listTable->createCell('contact_company', $row['contact_company']);
        echo $listTable->createCell('dept_name', $row['dept_name']);
    ?>
</tr>
<?php }

echo $listTable->endTable();