<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

$AppUI = new w2p_Core_CAppUI();
$AppUI->setStyle();
$uistyle = $AppUI->getPref('UISTYLE');

$msg = w2PgetParam($_GET, 'msg', '');

switch($msg) {
    case '':
        //No message, do nothing
        break;
    case 'data':
        $msg = "You didn't provide the correct Anti Spam Security ID or all required data. Please try again.";
        break;
    case 'spam':
        $msg = "You didn't provide the Anti Spam Security ID. Please try again.";
        break;
    case 'existing-user':
        $msg = 'The username you selected already exists, please select another or if that user name is yours request the password recovery through the dedicated link.';
        break;
    case 'existing-email':
        $msg = 'The email you selected already exists, please select another or if that email is yours request the password recovery through the dedicated link.';
        break;
    case 'user':
    case 'contact':
        $msg = "There was an error creating your $msg. No further information is available at this time.";
        break;
    case 'ok':
        $msg = 'The User Administrator has been notified to grant you access to the system and an email message was sent to you with your login info. Thank you very much.';
        break;
    default:
        $msg = 'No clue what you just tried, but stop it.';
}
$AppUI->setMsg($msg, UI_MSG_ERROR);

// Can not load the passwordstrength.js file via AppUI->addJavascriptFile()
// because AppUI->loadFooterJS() is never called...
?>
<script type="text/javascript" src="<?php echo W2P_BASE_URL; ?>/js/passwordstrength.js"></script>
<script language="javascript">
function submitIt(){
    var form = document.editFrm;
   if (form.user_username.value.length < <?php echo w2PgetConfig('username_min_len'); ?>) {
        alert("<?php echo $AppUI->_('Username size is invalid, should be greater than', UI_OUTPUT_JS); ?>" + ' ' + <?php echo w2PgetConfig('username_min_len'); ?>);
        form.user_username.focus();
    } else if (form.user_password.value.length < <?php echo w2PgetConfig('password_min_len'); ?>) {
        alert("<?php echo $AppUI->_('Password size is invalid, should be greater than', UI_OUTPUT_JS); ?>" + ' ' + <?php echo w2PgetConfig('password_min_len'); ?>);
        form.user_password.focus();
    } else if (form.user_password.value !=  form.password_check.value) {
        alert("<?php echo $AppUI->_('Password confirmation failed', UI_OUTPUT_JS); ?>");
        form.user_password.focus();
    } else if (form.contact_first_name.value.length < 1) {
        alert("<?php echo $AppUI->_('First name is invalid', UI_OUTPUT_JS); ?>");
        form.contact_first_name.focus();
    } else if (form.contact_last_name.value.length < 1) {
        alert("<?php echo $AppUI->_('Last name is invalid', UI_OUTPUT_JS); ?>");
        form.contact_last_name.focus();
    } else if (form.contact_email.value.length < 4) {
        alert("<?php echo $AppUI->_('Email is invalid', UI_OUTPUT_JS); ?>");
        form.contact_email.focus();
	} else {
        form.submit();
    }
}
</script>
<div class="d-flex justify-content-center">
	<a href="http://www.web2project.net" class="w2p-auth-logo"><img alt="web2Project Home" src="./style/<?php echo $uistyle; ?>/images/logo.png" width="123" height="28" /></a>
</div>
<div class="mx-auto" style="max-width: 44rem;">
    <?php echo $AppUI->getMsg(); ?>
</div>
<div class="mx-auto my-3" style="max-width: 44rem;">
	<h1 class="h4">New Signup to web2Project!</h1>
	Please enter the info below to create a new signup.
</div>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="./do_user_aed.php" method="post" accept-charset="utf-8">
	<input type="hidden" name="user_id" value="0" />
	<input type="hidden" name="contact_id" value="0" />
	<input type="hidden" name="username_min_len" value="<?php echo w2PgetConfig('username_min_len'); ?>" />
	<input type="hidden" name="password_min_len" value="<?php echo w2PgetConfig('password_min_len'); ?>" />
    <?php echo $form->addNonce(); ?>

    <?php echo $AppUI->getTheme()->styleRenderBoxTop(); ?>
    <div class="std addedit mx-auto" style="max-width: 44rem;">
        <div class="column left w2p-wide">
            <p>
                <label>* <?php echo $AppUI->_('Login Name'); ?>:</label>
                <input type="text" class="text" name="user_username" value="" maxlength="255" size="40" />
            </p>
            <p>
                <label>* <?php echo $AppUI->_('Password'); ?>:</label>
                <input type="password" class="text" name="user_password" value="" maxlength="32" size="32" onKeyUp="checkPassword(this.value);" />
            </p>
            <p>
                <label>* <?php echo $AppUI->_('Confirm Password'); ?>:</label>
                <input type="password" class="text" name="password_check" value="" maxlength="32" size="32" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Password Strength'); ?></label>
                <span class="d-inline-block border rounded" style="width: 135px; height: 1rem;"><span id="progressBar" class="d-block h-100" style="font-size: 1px; width: 0px;"></span></span>
            </p>
            <p>
                <label>* <?php echo $AppUI->_('Name'); ?>:</label>
                <input type="text" class="text" name="contact_first_name" value="" maxlength="25" />
                <input type="text" class="text" name="contact_last_name" value="" maxlength="25" />
            </p>
            <p>
                <label>* <?php echo $AppUI->_('Email'); ?>:</label>
                <input type="text" class="text" name="contact_email" value="" maxlength="255" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>:</label>
                <input type="text" class="text" name="contact_phone" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>1:</label>
                <input type="text" class="text" name="contact_address1" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>2:</label>
                <input type="text" class="text" name="contact_address2" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('City'); ?>:</label>
                <input type="text" class="text" name="contact_city" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('State'); ?>:</label>
                <input type="text" class="text" name="contact_state" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Postcode') . ' / ' . $AppUI->_('Zip Code'); ?>:</label>
                <input type="text" class="text" name="contact_zip" value="" maxlength="50" size="40" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                    $countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountries');
                    echo arraySelect($countries, 'contact_country', 'size="1" class="text"', 0);
                ?>
            </p>
            <p>* <?php echo $AppUI->_('Required Fields'); ?></p>
        </div>
        <div class="w2p-form-actions">
            <input type="button" value="<?php echo $AppUI->_('cancel'); ?>" onclick="history.go(-1)" class="button" />
            <input type="button" value="<?php echo $AppUI->_('sign me up!'); ?>" onclick="submitIt()" class="button save" />
        </div>
    </div>
    <?php echo $AppUI->getTheme()->styleRenderBoxBottom(); ?>
</form>