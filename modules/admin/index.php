<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('UserIdxTab', $_GET, 'tab', 0);

$perms = &$AppUI->acl();
if (!canView('admin')) {
	$AppUI->redirect(ACCESS_DENIED);
}
if (!canView('users')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$AppUI->savePlace();

if (isset($_GET['stub'])) {
	$AppUI->setState('UserIdxStub', w2PgetParam($_GET, 'stub', null));
	$AppUI->setState('UserIdxWhere', '');
} elseif (isset($_POST['where'])) {
	$AppUI->setState('UserIdxWhere', $_POST['where']);
	$AppUI->setState('UserIdxStub', '');
}
$stub = $AppUI->getState('UserIdxStub');
$where = $AppUI->getState('UserIdxWhere');
$where = w2PformSafe($where, true);

if (isset($_GET['orderby'])) {
	$AppUI->setState('UserIdxOrderby', w2PgetParam($_GET, 'orderby', null));
}
$orderby = $AppUI->getState('UserIdxOrderby') ? $AppUI->getState('UserIdxOrderby') : 'user_username';
$orderby = ($tab == 3 || ($orderby != 'date_time_in' && $orderby != 'user_ip')) ? $orderby : 'user_username';

// Pull First Letters
$letters = CUser::getFirstLetters();
$letters = $letters.CContact::getFirstLetters($AppUI->user_id, true);

$a2z = '<a href="./index.php?m=admin&stub=0">' . $AppUI->_('All') . '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
for ($c = 65; $c < 91; $c++) {
	$cu = chr($c);
	$cell = !(mb_strpos($letters, $cu) === false) ? '<a href="?m=admin&stub=' . $cu . '">' . $cu . '</a>' : '<font color="#999999">' . $cu . '</font>';
	$a2z .= $cell.'&nbsp;';
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('User Management', 'helix-setup-users.png', $m, "$m.$a");
$titleBlock->addCell('<form action="index.php?m=admin" method="post" accept-charset="utf-8">' .
        '<input type="text" name="where" class="text" size="10" value="' . $where . '" /></form>');
$titleBlock->addCell($AppUI->_('Search') . ':');
$titleBlock->addCell($a2z);
$titleBlock->show();

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delMe( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('User', UI_OUTPUT_JS); ?> " + y + "?" )) {
		document.frmDelete.user_id.value = x;
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>

<?php
$extra = '<td align="right" width="100%"><input type="button" class=button value="' . $AppUI->_('add user') . '" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>';

$tabBox = new CTabBox('?m=admin', W2P_BASE_DIR . '/modules/admin/', $tab);
$tabBox->add('vw_active_usr', 'Active Users');
$tabBox->add('vw_inactive_usr', 'Inactive Users');
$tabBox->add('vw_usr_log', 'User Log');
if ($canEdit && $canDelete) {
	$tabBox->add('vw_usr_sessions', 'Active Sessions');
}
$tabBox->show($extra);

?>

<form name="frmDelete" action="./index.php?m=admin" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="0" />
</form>