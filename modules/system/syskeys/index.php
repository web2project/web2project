<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
$canEdit = canEdit('system');
if (!$canEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

global $fixedSysVals;

$keys = __extract_from_syskeys_index1();

$values = __extract_from_syskeys_index2();

$vals = __extract_from_syskeys_index3();

foreach ($values as $key => $value) {
	$values[$key]['sysval_value'] = '';
	foreach ($vals as $kval => $val) {
		if ($value['sysval_title'] == $val['sysval_title']) {
			$sep1 = $val['syskey_sep1'];
			$sep2 = $val['syskey_sep2'];
			if (!isset($sep1) || empty($sep1)) {
				$sep1 = "\n";
			}
			if ($sep1 == "\\n") {
				$sep1 = "\n";
			}
			if ($sep1 == "\\r") {
				$sep1 = "\r";
			}
			$values[$key]['sysval_value'] .= $val['sysval_value_id'] . $sep2 . $val['sysval_value'] . $sep1;
		}
	}
}

$sysval_id = isset($_GET['sysval_id']) ? w2PgetParam($_GET, 'sysval_id', '') : '';

$titleBlock = new w2p_Theme_TitleBlock('System Lookup Values', 'myevo-weather.png', $m);
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
		f = document.sysValFrm;
		f.del.value = 1;
		f.sysval_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>
<form name="sysValFrm" method="post" action="?m=system&u=syskeys" accept-charset="utf-8">
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_sysval_aed" />
    <table class="tbl list sysvals">
        <tr>
            <th>&nbsp;</th>
            <th><?php echo $AppUI->_('Key Type'); ?></th>
            <th><?php echo $AppUI->_('Title'); ?></th>
            <th colspan="2"><?php echo $AppUI->_('Values'); ?></th>
            <th>&nbsp;</th>
        </tr>
        <?php
        $canEdit = canEdit('system');
        foreach ($values as $row) {
            echo showRow($row['sysval_title'], $row['sysval_key_id'], $row['sysval_title'], $row['sysval_value']);
        }
        // add in the new key row:
        if (!$sysval_id) {
            echo showRow();
        }
        ?>
    </table>
</form>