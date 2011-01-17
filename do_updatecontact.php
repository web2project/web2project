<?php /* $Id$ $URL$ */
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, "WIN") !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';

$AppUI = new CAppUI();

$msg = '';

$updatekey = w2PgetParam($_POST, 'updatekey', 0);
$contactkey = CContact::getContactByUpdatekey($updatekey);

$contact = new CContact();
$q = new w2p_Database_Query;

$contact_id = $contactkey ? $contactkey : 0;

// check permissions for this record

if (!$contact_id) {
	echo $AppUI->_('You are not authorized to use this page. If you should be authorized please contact Bruce Bodger to give you another valid link, thank you.');
	exit;
}

if (!$contact->bind($_POST)) {
	$AppUI->setMsg($contact->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Contact');

$isNotNew = $_POST['contact_id'];

if (($msg = $contact->store($AppUI))) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
	$msg = $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
} else {
	$custom_fields = new w2p_Core_CustomFields('contacts', 'addedit', $contact->contact_id, 'edit', 1);
	$custom_fields->bind($_POST);
	$custom_fields->store($contact->contact_id);
	
	$contact->clearUpdateKey();

	$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
	echo $AppUI->_('Your contact data has been recorded successfully. Your may now close your browser windoq.  Thank you very much, ' . $contact->contact_first_name);
}
?>
<html>
	<body>
		<?php echo $msg; ?>
	</body>
</html>