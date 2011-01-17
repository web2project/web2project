<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

global $fixedSysVals;
$AppUI->savePlace();

// pull all the key types
$q = new w2p_Database_Query;
$q->addTable('syskeys');
$q->addQuery('syskey_id,syskey_name');
$q->addOrder('syskey_name');
$keys = arrayMerge(array(0 => '- Select Type -'), $q->loadHashList());
$q->clear();

$q = new w2p_Database_Query;
$q->addTable('syskeys');
$q->addTable('sysvals');
$q->addQuery('DISTINCT sysval_title, sysval_key_id, syskeys.*');
$q->addWhere('sysval_key_id = syskey_id');
$q->addOrder('sysval_title');
$q->addOrder('sysval_id');
$values = $q->loadList();
$q->clear();

$q = new w2p_Database_Query;
$q->addTable('sysvals');
$q->addTable('syskeys');
$q->addQuery('sysval_title, sysval_value_id, sysval_value, syskey_sep1, syskey_sep2');
$q->addWhere('sysval_key_id = syskey_id');
$q->addOrder('sysval_title');
$q->addOrder('sysval_id');
$vals = $q->loadList();
$q->clear();

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

$titleBlock = new CTitleBlock('System Lookup Values', 'myevo-weather.png', $m, $m . '.' . $u . '.' . $a);
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

<form name="sysValFrm" method="post" action="?m=system&u=syskeys&a=do_sysval_aed" accept-charset="utf-8">
  <input type="hidden" name="del" value="0" />
  <table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
    <tr>
    	<th>&nbsp;</th>
    	<th><?php echo $AppUI->_('Key Type'); ?></th>
    	<th><?php echo $AppUI->_('Title'); ?></th>
    	<th colspan="2"><?php echo $AppUI->_('Values'); ?></th>
    	<th>&nbsp;</th>
    </tr>
    <?php
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

<?php

function showRow($id = '', $key = 0, $title = '', $value = '') {
  global $canEdit, $sysval_id, $AppUI, $keys;
  global $fixedSysVals;
  $s = '';
  if (($sysval_id == $title) && $canEdit) {
    // edit form
    $s .= '<tr><td><input type="hidden" name="sysval_id" value="' . $title . '" />&nbsp;</td>';
    $s .= '<td valign="top"><a name="'.$title.'"> </a>' . arraySelect($keys, 'sysval_key_id', 'size="1" class="text"', $key) . '</td>';
    $s .= '<td valign="top"><input type="text" name="sysval_title" value="' . w2PformSafe($title) . '" class="text" /></td>';
    $s .= '<td valign="top"><textarea name="sysval_value" class="small" rows="5" cols="40">' . $value . '</textarea></td>';
    $s .= '<td><input type="submit" value="' . $AppUI->_($id ? 'save' : 'add') . '" class="button" /></td><td>&nbsp;</td>';
  } else {
    $s = '<tr><td width="12" valign="top">';
    if ($canEdit) {
      $s .= '<a href="?m=system&u=syskeys&sysval_id=' . $title . '#'.$title.'" title="' . $AppUI->_('edit') . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a></td>';
    }
    $s .= '<td valign="top">' . $keys[$key] . '</td>';
    $s .= '<td valign="top">' . w2PformSafe($title) . '</td>';
    $s .= '<td valign="top" colspan="2">' . $value . '</td>';
    $s .= '<td valign="top" width="16">';
    if ($canEdit && !in_array($title, $fixedSysVals)) {
      $s .= '<a href="javascript:delIt(\'' . $title . '\')" title="' . $AppUI->_('delete') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
    }
    $s .= '</td>';
  }
  $s .= '</tr>';
  return $s;
}