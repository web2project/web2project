<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$dialog = (int) w2PgetParam($_GET, 'dialog', 0);

$inc = W2P_BASE_DIR . '/locales/' . $AppUI->user_locale . '/help.php';

if (!file_exists($inc)) {
	$inc = W2P_BASE_DIR . '/locales/en/help.hlp';
}
?>
    <style>
        div[class="std titlebar"], form[name="frm_new"],
        body div:nth-child(2), div[class="left"] {
            <?php if (1 == $dialog) { ?> display: none; <?php } ?>
        }
    </style>
<?php
include $inc;