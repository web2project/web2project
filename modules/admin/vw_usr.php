<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI;

$display_last_login = !((int) w2PgetParam($_REQUEST, 'tab', 0));

$fieldList = array();
$fieldNames = array();

$fields = w2p_Core_Module::getSettings('users', 'index_table');

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
/*
* TODO: This is an oddity because the inserted column (login history) has to
*   get inserted as the *second* entry instead of the first.. ugh.
*
*/
array_unshift($fieldNames,  '');
?>
<table class="tbl list">
    <tr>
        <?php foreach ($fieldNames as $index => $name) { ?>
            <th><?php echo $AppUI->_($fieldNames[$index]); ?></th>
        <?php } ?>
    </tr>
<?php

$htmlHelper = new w2p_Output_HTMLHelper($AppUI);

$types = w2PgetSysVal('UserType');
$customLookups = array('user_type' => $types);

$perms = &$AppUI->acl();
foreach ($users as $row) {
	if ($perms->isUserPermitted($row['user_id']) != $canLogin) {
		continue;
	}
    $htmlHelper->stageRowData($row);
?>
<tr>
	<td width="30" align="center" nowrap="nowrap">
        <?php if ($canEdit) { ?>
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td>
				<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row['user_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
					<?php echo w2PshowImage('icons/stock_edit-16.png', 16, 16, ''); ?>
				</a>
			</td>
			<td>
				<a href="?m=admin&a=viewuser&user_id=<?php echo $row['user_id']; ?>&tab=1" title="">
					<img src="<?php echo w2PfindImage('obj/lock.gif'); ?>" width="16" height="16" border="0" alt="<?php echo $AppUI->_('edit permissions'); ?>" />
				</a>
			</td>
			<td>
				<a href="javascript:delMe(<?php echo $row['user_id']; ?>, '<?php echo addslashes($row['contact_display_name']); ?>')" title="<?php echo $AppUI->_('delete'); ?>">
					<?php echo w2PshowImage('icons/stock_delete-16.png', 16, 16, ''); ?>
				</a>
			</td>
		</tr>
		</table>
        <?php } ?>
	</td>
	<?php if (w2PgetParam($_REQUEST, 'tab', 0) == 0) { ?>
	<td nowrap="nowrap">
	       <?php
		$q = new w2p_Database_Query;
		$q->addTable('user_access_log', 'ual');
		$q->addQuery('user_access_log_id, ( unix_timestamp( \''.$q->dbfnNowWithTZ().'\' ) - unix_timestamp( date_time_in ) ) / 3600 as 		hours, ( unix_timestamp( \''.$q->dbfnNowWithTZ().'\' ) - unix_timestamp( date_time_last_action ) ) / 3600 as idle, if(isnull(date_time_out) or date_time_out =\'0000-00-00 00:00:00\',\'1\',\'0\') as online');
		$q->addWhere('user_id = ' . (int)$row['user_id']);
		$q->addOrder('user_access_log_id DESC');
		$q->setLimit(1);
		$user_logs = $q->loadList();

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
		<a href="mailto:<?php echo $row['contact_email']; ?>"><img src="<?php echo w2PfindImage('obj/email.gif'); ?>" width="16" height="16" border="0" alt="email" /></a>
        <?php echo $row['contact_display_name']; ?>
	</td>
    <?php
        echo $htmlHelper->createCell('user_name', $row['user_username']);
        //echo $htmlHelper->createCell('user_type', $row['user_type'], $customLookups);
        echo $htmlHelper->createCell('contact_company', $row['contact_company']);
        echo $htmlHelper->createCell('dept_name', $row['dept_name']);
    ?>
</tr>
<?php } ?>

</table>