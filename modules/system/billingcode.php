<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## add or edit a user preferences
##
$company_id = 0;
$company_id = isset($_REQUEST['company_id']) ? w2PgetParam($_REQUEST, 'company_id', 0) : 0;
// Check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$q = new w2p_Database_Query;
$q->addTable('billingcode', 'bc');
$q->addQuery('billingcode_id, billingcode_name, billingcode_value, billingcode_desc, billingcode_status');
$q->addOrder('billingcode_name ASC');
$q->addWhere('company_id = ' . (int)$company_id);
$billingcodes = $q->loadList();
$q->clear();

$q = new w2p_Database_Query;
$q->addTable('companies', 'c');
$q->addQuery('company_id, company_name');
$q->addOrder('company_name ASC');
$company_list = $q->loadHashList();
$company_list[0] = $AppUI->_('Select Company');
$q->clear();

$company_name = $company_list[$company_id];

$titleBlock = new CTitleBlock('Edit Billing Codes', 'myevo-weather.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
<!--
function submitIt(){
	var form = document.changeuser;
	form.submit();
}

function changeIt() {
	var f=document.changeMe;
	var msg = '';
	f.submit();
}


function delIt2(id) {
	document.frmDel.billingcode_id.value = id;
	document.frmDel.submit();
}
-->
</script>

<form name="frmDel" action="./index.php?m=system" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_billingcode_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	<input type="hidden" name="billingcode_id" value="" />
</form>
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<tr>
	<td>
	<form name="changeMe" action="./index.php?m=system&amp;a=billingcode" method="post" accept-charset="utf-8">
		<?php echo arraySelect($company_list, 'company_id', 'size="1" class="text" onchange="changeIt();"', $company_id, false); ?>
	</form>
	</td>
</tr>
<tr>
	<th width="40">&nbsp;
	<form name="changeuser" action="./index.php?m=system" method="post" accept-charset="utf-8">
		<input type="hidden" name="dosql" value="do_billingcode_aed" />
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
		<input type="hidden" name="billingcode_status" value="0" />
	</th>
	<th><?php echo $AppUI->_('Billing Code'); ?></th>
	<th><?php echo $AppUI->_('Value'); ?></th>
	<th><?php echo $AppUI->_('Description'); ?></th>
</tr>

<?php
foreach ($billingcodes as $code) {
	showcodes($code);
}

if (isset($_GET['billingcode_id'])) {
	$q->addQuery('*');
	$q->addTable('billingcode');
	$q->addWhere('billingcode_id = ' . (int)w2PgetParam($_GET, 'billingcode_id', 0));
	list($obj) = $q->loadList();

	echo '
<tr>
	<td>&nbsp;<input type="hidden" name="billingcode_id" value="' . w2PgetParam($_GET, 'billingcode_id', 0) . '" /></td>
	<td><input type="text" class="text" name="billingcode_name" value="' . $obj['billingcode_name'] . '" /></td>
	<td><input type="text" class="text" name="billingcode_value" value="' . $obj['billingcode_value'] . '" /></td>
	<td><input type="text" class="text" name="billingcode_desc" value="' . $obj['billingcode_desc'] . '" /></td>
</tr>';
} else {
?>
<tr>
	<td>&nbsp;</td>
	<td><input type="text" class="text" name="billingcode_name" value="" /></td>
	<td><input type="text" class="text" name="billingcode_value" value="" /></td>
	<td><input type="text" class="text" name="billingcode_desc" value="" /></td>
</tr>
<?php } ?>

<tr>
	<td align="left">
		<input class="button"  type="button" value="<?php echo $AppUI->_('back'); ?>" onclick="javascript:history.back(-1);" />
	</td>
	<td colspan="3" align="right">
		<input class="button" type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
	</td>
</tr>
</table>
</form>