<?php

require_once 'bootstrap.php';

$updatekey = w2PgetParam($_POST, 'updatekey', 0);
$updatekey = preg_replace("/[^A-Za-z0-9]/", "", $updatekey);
$contact_id = (int) CContact::getContactByUpdatekey($updatekey);

if (!$contact_id) {
	echo $AppUI->_('You are not authorized to use this page. If you should be authorized please contact the sender to give you another valid link, thank you.');
	exit;
}

$contact = new CContact();
if (!$contact->bind($_POST)) {
	$msg = $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
} else {

    $result = $contact->store();

    if (!$result) {
        $msg = $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
    } else {
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