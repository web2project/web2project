<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (!($user_id = w2PgetParam($_REQUEST, 'user_id', 0))) {
	$user_id = $AppUI->user_id;
}

// check for a non-zero user id
if ($user_id) {
	$old_pwd = (new w2p_Database_Connection($GLOBALS['db']))->escape(trim(w2PgetParam($_POST, 'old_pwd', null)));
	$new_pwd1 = (new w2p_Database_Connection($GLOBALS['db']))->escape(trim(w2PgetParam($_POST, 'new_pwd1', null)));
	$new_pwd2 = (new w2p_Database_Connection($GLOBALS['db']))->escape(trim(w2PgetParam($_POST, 'new_pwd2', null)));

	$perms = &$AppUI->acl();
	$canAdminEdit = canEdit('system');

	// has the change form been posted
	if ($new_pwd1 && $new_pwd2 && $new_pwd1 == $new_pwd2) {
		$user = new CUser();

		if ($canAdminEdit || $user->validatePassword($user_id, $old_pwd)) {
            $user->load($user_id);
			$user->user_password = $new_pwd1;
            $result = $user->store();

            if ($result) {
                ?>
                <script language="javascript" type="text/javascript">
                    window.onload = function() {
                        window.close();
		            }
                </script>
                <?php
			} else {
                echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
                echo $AppUI->getTheme()->styleRenderBoxTop();
				echo '<div class="std p-2">' . $AppUI->_('chgpwUpdated') . '</div>';
			}
		} else {
			echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
            echo $AppUI->getTheme()->styleRenderBoxTop();
			echo '<div class="std p-2">' . $AppUI->_('chgpwWrongPW') . '</div>';
		}
	} else {

            $AppUI->getTheme()->addFooterJavascriptFile('js/passwordstrength.js');
            
		?>
		<script language="javascript" type="text/javascript">
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
        echo $AppUI->getTheme()->styleRenderBoxTop();
		?>
		<form name="frmEdit" method="post" onsubmit="return false" accept-charset="utf-8">
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
			<div class="std addedit">
				<div class="column left">
				<?php if (!$canAdminEdit) { ?>
					<p>
						<label><?php echo $AppUI->_('Current Password'); ?></label>
						<input type="password" name="old_pwd" class="text" />
					</p>
				<?php } ?>
				<p>
					<label><?php echo $AppUI->_('New Password'); ?></label>
					<input type="password" name="new_pwd1" class="text" onKeyUp="checkPassword(this.value);" />
				</p>
				<p>
					<label><?php echo $AppUI->_('Repeat New Password'); ?></label>
					<input type="password" name="new_pwd2" class="text" />
				</p>
				<p>
					<label><?php echo $AppUI->_('Password Strength'); ?></label>
					<span class="d-inline-block border rounded" style="width: 135px; height: 1rem;">
						<span id="progressBar" class="d-block h-100"></span>
					</span>
				</p>
				<p class="justify-content-end">
					<input type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" class="button" />
				</p>
				</div>
			</div>
		</form>
		<?php
	}
} else {
	echo '<h1>' . $AppUI->_('Change User Password') . '</h1>';
    echo $AppUI->getTheme()->styleRenderBoxTop();
	echo '<div class="std p-2">' . $AppUI->_('chgpwLogin') . '</div>';
}
