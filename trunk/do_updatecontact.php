<?php /* $Id$ $URL$ */
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';

if (!isset($GLOBALS['OS_WIN'])) {
	$GLOBALS['OS_WIN'] = (stristr(PHP_OS, "WIN") !== false);
}

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
$AppUI = new CAppUI();
require_once W2P_BASE_DIR . '/classes/date.class.php';
require_once W2P_BASE_DIR . '/modules/contacts/contacts.class.php';

$obj = new CContact();
$msg = '';

$updatekey = w2PgetParam($_POST, 'updatekey', 0);
$q = new DBQuery;
$q->addTable('contacts');
$q->addQuery('contact_id');
$q->addWhere('contact_updatekey = \'' . $updatekey . '\'');
$contactkey = $q->loadList();
$q->clear();

$contact_id = $contactkey[0]['contact_id'] ? $contactkey[0]['contact_id'] : 0;

// check permissions for this record

if (!$contact_id) {
	echo ($AppUI->_('You are not authorized to use this page. If you should be authorized please contact Bruce Bodger to give you another valid link, thank you.'));
	exit;
}

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}
require_once W2P_BASE_DIR . '/classes/CustomFields.class.php';

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg('Contact');

$isNotNew = $_POST['contact_id'];

if (($msg = $obj->store())) {
	$AppUI->setMsg($msg, UI_MSG_ERROR);
	echo $AppUI->_('There was an error recording your contact data, please contact the system administrator. Thank you very much.');
} else {
	$custom_fields = new CustomFields('contacts', 'addedit', $obj->contact_id, 'edit', 1);
	$custom_fields->bind($_POST);
	$sql = $custom_fields->store($obj->contact_id); // Store Custom Fields

	$rnow = new CDate();
	$obj->contact_updatekey = '';
	$obj->contact_lastupdate = $rnow->format(FMT_DATETIME_MYSQL);
	$obj->store();

	$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
	//            echo $AppUI->_('Your contact data has been recorded sucessfully. Thank you very much.');
	//            echo "<script>if(confirm('".$AppUI->_('Your contact data has  been recorded sucessfully. Thank you very much.')."')){self.close();} else {self.close();};</script>";
	echo ('Your contact data has been recorded successfully. Your may now close your browser window<br></br>Thank you very much, ' . $obj->contact_first_name);
}
?>