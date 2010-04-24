<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (!($user_id = w2PgetParam($_REQUEST, 'user_id', 0))) {
	$user_id = $AppUI->user_id;
}

// check for a non-zero user id
if ($user_id) {
	$old_pwd = db_escape(trim(w2PgetParam($_POST, 'old_pwd', null)));
	$new_pwd1 = db_escape(trim(w2PgetParam($_POST, 'new_pwd1', null)));
	$new_pwd2 = db_escape(trim(w2PgetParam($_POST, 'new_pwd2', null)));

	$perms = &$AppUI->acl();
	$canAdminEdit = canEdit('admin');

	// has the change form been posted
	if ($new_pwd1 && $new_pwd2 && $new_pwd1 == $new_pwd2) {
		$user = new CUser();

		if ($canAdminEdit || $user->validatePassword($user_id, $old_pwd)) {
			$user->user_id = $user_id;
			$user->user_password = $new_pwd1;

			if (($msg = $user->store())) {
				$AppUI->setMsg($msg, UI_MSG_ERROR);
			} else {
				echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
				if (function_exists('styleRenderBoxTop')) {
					echo styleRenderBoxTop();
				}
				echo '<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std"><tr><td>' . $AppUI->_('chgpwUpdated') . '</td></tr></table>';
			}
		} else {
			echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
			if (function_exists('styleRenderBoxTop')) {
				echo styleRenderBoxTop();
			}
			echo '<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std"><tr><td>' . $AppUI->_('chgpwWrongPW') . '</td></tr></table>';
		}
	} else {
		?>
		<script language="javascript">
		function submitIt() {
			var f = document.frmEdit;
			var msg = '';
		
			if (f.new_pwd1.value.length < <?php echo w2PgetConfig('password_min_len'); ?>) {
		        	msg += "<?php echo $AppUI->_('chgpwValidNew', UI_OUTPUT_JS); ?>" + <?php echo w2PgetConfig('password_min_len'); ?>;
					f.new_pwd1.focus();
			}
			if (f.new_pwd1.value != f.new_pwd2.value) {
				msg += "\n<?php echo $AppUI->_('chgpwNoMatch', UI_OUTPUT_JS); ?>";
				f.new_pwd2.focus();
			}
			if (msg.length < 1) {
				f.submit();
			} else {
				alert(msg);
			}
		}
		</script>
		<h1><?php echo $AppUI->_('Change User Password'); ?></h1>
		<?php
			if (function_exists('styleRenderBoxTop')) {
				echo styleRenderBoxTop();
			}
		?>
		<form name="frmEdit" method="post" onsubmit="return false" accept-charset="utf-8">
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
			<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std">
				<?php if (!$canAdminEdit) { ?>
					<tr>
						<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Current Password'); ?></td>
						<td><input type="password" name="old_pwd" class="text" /></td>
					</tr>
				<?php } ?>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('New Password'); ?></td>
					<td><input type="password" name="new_pwd1" class="text" /></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Repeat New Password'); ?></td>
					<td><input type="password" name="new_pwd2" class="text" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right" nowrap="nowrap"><input type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" class="button" /></td>
				</tr>
			</table>
		<form accept-charset="utf-8">
		<?php
	}
} else {
	echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
	echo '<table width="100%" cellspacing="0" cellpadding="4" border="0" class="std"><tr><td>' . $AppUI->_('chgpwLogin') . '</td></tr></table>';
}