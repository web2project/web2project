<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $addPwT, $company_id, $dept_ids, $department, $min_view, $m, $a;
$user_id = isset($_GET['user_id']) ? w2PgetParam($_GET, 'user_id', 0) : 0;

if ($user_id != $AppUI->user_id && (!$perms->checkModuleItem('admin', 'view', $user_id) || !$perms->checkModuleItem('users', 'view', $user_id))) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();

$addPwT = $AppUI->processIntState('addProjWithTasks', $_POST, 'add_pwt', 0);

$company_id = $AppUI->getState('UsrProjIdxCompany') !== null ? $AppUI->getState('UsrProjIdxCompany') : $AppUI->user_company;

$company_prefix = 'company_';

if (isset($_POST['department'])) {
	$AppUI->setState('UsrProjIdxDepartment', $_POST['department']);

	//if department is set, ignore the company_id field
	unset($company_id);
}
$department = $AppUI->getState('UsrProjIdxDepartment') !== null ? $AppUI->getState('UsrProjIdxDepartment') : $company_prefix . $AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the
// $department variable, and populate the $company_id variable.
if (!(strpos($department, $company_prefix) === false)) {
	$company_id = substr($department, strlen($company_prefix));
	$AppUI->setState('UsrProjIdxCompany', $company_id);
	unset($department);
}

$tab = $AppUI->processIntState('UserVwTab', $_GET, 'tab', 0);

// pull data
$user = new CUser();
$user->loadFull($user_id);

$contact = new CContact();
$contact->contact_id = $user->user_contact;
$methods = $contact->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

if (!$user) {
	$titleBlock = new CTitleBlock('Invalid User ID', 'helix-setup-user.png', $m, "$m.$a");
	$titleBlock->addCrumb('?m=admin', 'users list');
	$titleBlock->show();
} else {
	$countries = w2PgetSysVal('GlobalCountries');
	// setup the title block
	$titleBlock = new CTitleBlock('View User', 'helix-setup-user.png', $m, "$m.$a");
	if ($canRead) {
		$titleBlock->addCrumb('?m=admin', 'users list');
	}
	if ($canEdit || $user_id == $AppUI->user_id) {
		$titleBlock->addCrumb('?m=admin&a=addedituser&user_id='.$user_id, 'edit this user');
		$titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$user->contact_id, 'edit this contact');
		$titleBlock->addCrumb('?m=system&a=addeditpref&user_id='.$user_id, 'edit preferences');
		$titleBlock->addCrumbRight('<div class="crumb"><ul style="float:right;"><li><a href="javascript: void(0);" onclick="popChgPwd();return false"><span>' . $AppUI->_('change password') . '</span></a></li></ul></div>');
		$titleBlock->addCell('<td align="right" width="100%"><input type="button" class=button value="' . $AppUI->_('add user') . '" onclick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>');
	}
	$titleBlock->show();
?>
<script language="javascript">
	<?php
		// security improvement:
		// some javascript functions may not appear on client side in case of user not having write permissions
		// else users would be able to arbitrarily run 'bad' functions
		if ($canEdit || $user_id == $AppUI->user_id) {
	?>
	function popChgPwd() {
		window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $user->user_id; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scrollbars=no' );
	}
	<?php } ?>
</script>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Login Name'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $user->user_username; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('User Type'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $AppUI->_($utypes[$user->user_type]); ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Real Name'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $user->contact_first_name . ' ' . $user->contact_last_name; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=companies&a=view&company_id=<?php echo $user->contact_company; ?>"><?php echo $user->company_name; ?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=departments&a=view&dept_id=<?php echo $user->contact_department; ?>"><?php echo $user->dept_name; ?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $user->contact_phone; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Home Phone'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $user->contact_phone2; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Mobile'); ?>:</td>
			<td class="hilite" width="100%"><?php echo $user->contact_mobile; ?></td>
		</tr>
		<tr valign="top">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Address'); ?>:</td>
			<td class="hilite" width="100%">
				<?php echo $user->contact_address1; ?><br />
                <?php echo ($user->contact_address2 == '') ? '' : $user->contact_address2.'<br />'; ?>
                <?php echo $user->contact_city . ', ' . $user->contact_state . ' ' . $user->contact_zip; ?><br />
                <?php echo isset($countries[$user->contact_country]) ? $countries[$user->contact_country] : $user->contact_country; ?>
			</td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
            <tr>
                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Birthday'); ?>:</td>
                <td class="hilite" width="100%">
                    <?php
                    $bday = new CDate($user->contact_birthday);
                    $df = $AppUI->getPref('SHDATEFORMAT');
                    echo $bday->format($df);
                    ?>
                </td>
            </tr>
            <?php foreach ($methods as $method => $value): ?>
                <tr>
                    <td align="right" width="100" nowrap="nowrap"><?php echo $AppUI->_($methodLabels[$method]); ?>:</td>
                    <td class="hilite" width="100%"><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Calendar Feed'); ?>:</td>
                <td class="hilite" width="100%">
                    <?php if ($user->feed_token != '') {
                        $calendarFeed = W2P_BASE_URL.'/calendar.php?token='.$user->feed_token;
                        ?>
                        <a href="<?php echo $calendarFeed; ?>">calendar feed</a>
                    <?php } ?>
                    &nbsp;&nbsp;&nbsp;
                    <form name="regenerateToken" action="./index.php?m=admin" method="post" accept-charset="utf-8">
                        <input type="hidden" name="user_id" value="<?php echo intval($user->user_id); ?>" />
                        <input type="hidden" name="dosql" value="do_user_token" />
                        <input type="hidden" name="token" value="<?php echo $user->feed_token; ?>" />
                        <input type="submit" name="regenerate token" value="<?php echo $AppUI->_('regenerate feed url'); ?>" />
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong><?php echo $AppUI->_('Signature'); ?>:</strong></td>
            </tr>
            <tr>
                <td class="hilite" width="100%" colspan="2">
                    <?php echo w2p_textarea($user->user_signature); ?>&nbsp;
                </td>
            </tr>
		</table>
	</td>
</tr>
</table>

<?php
	// tabbed information boxes
	$min_view = true;
	$tabBox = new CTabBox('?m=admin&a=viewuser&user_id='.$user_id, '', $tab);
	$tabBox->add(W2P_BASE_DIR . '/modules/admin/vw_usr_log', 'User Log');
	$tabBox->add(W2P_BASE_DIR . '/modules/admin/vw_usr_perms', 'Permissions');
	$tabBox->add(W2P_BASE_DIR . '/modules/admin/vw_usr_roles', 'Roles');
	$tabBox->show();
}