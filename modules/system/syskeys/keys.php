<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$q = new w2p_Database_Query;
$q->addTable('syskeys');
$q->addQuery('*');
$q->addOrder('syskey_name');
$keys = $q->loadList();
$q->clear();

$syskey_id = (int) w2PgetParam($_GET, 'syskey_id', 0);

$titleBlock = new CTitleBlock('System Lookup Keys', 'myevo-weather.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.sysKeyFrm;
		f.del.value = 1;
		f.syskey_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Name'); ?></th>
	<th colspan="2"><?php echo $AppUI->_('Label'); ?></th>
	<th>&nbsp;</th>
</tr>

<?php
// do the modules that are installed on the system
$s = '';
foreach ($keys as $row) {
	echo showRow_keys($row['syskey_id'], $row['syskey_name'], $row['syskey_label']);
}
// add in the new key row:
if ($syskey_id == 0) {
	echo showRow_keys();
}
?>
</table>