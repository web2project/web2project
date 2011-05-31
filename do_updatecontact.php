<?php /* $Id$ $URL$ */
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';

$AppUI = new CAppUI();

$updatekey = w2PgetParam($_POST, 'updatekey', 0);
$contact_id = (int) CContact::getContactByUpdatekey($updatekey);

if (!$contact_id) {
	echo $AppUI->_('You are not authorized to use this page. If you should be authorized please contact the sender to give you another valid link, thank you.');
	exit;
}

$contact = new CContact();
if (!$contact->bind($_POST)) {
	$msg = $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
} else {

    $result = $contact->store($AppUI);

    if (is_array($result)) {
        $msg = $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
    } else {
        $custom_fields = new w2p_Core_CustomFields('contacts', 'addedit', $contact->contact_id, 'edit', 1);
        $custom_fields->bind($_POST);
        $custom_fields->store($contact->contact_id);
        $contact->clearUpdateKey();

        $msg = $AppUI->_('Your contact data has been recorded successfully. Your may now close your browser window.  Thank you very much, ' . $contact->contact_first_name);
    }
}
?>
<html>
	<body>
		<?php echo $msg; ?>
	</body>
</html>