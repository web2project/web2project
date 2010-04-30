<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage authenticators
 */

/**
 * PostNuke authentication has encoded information
 * passed in on the login request.  This needs to
 * be extracted and verified.
 */
class w2p_Authenticators_PostNuke extends w2p_Authenticators_SQL {

	public function __construct() {
		global $w2Pconfig;
		$this->fallback = isset($w2Pconfig['postnuke_allow_login']) ? $w2Pconfig['postnuke_allow_login'] : false;
	}

	public function authenticate($username, $password) {
		global $db, $AppUI;
		if (!isset($_REQUEST['userdata'])) { // fallback to SQL Authentication if PostNuke fails.
			if ($this->fallback) {
				return parent::authenticate($username, $password);
			} else {
				die($AppUI->_('You have not configured your PostNuke site correctly'));
			}
		}

		if (!$compressed_data = base64_decode(urldecode($_REQUEST['userdata']))) {
			die($AppUI->_('The credentials supplied were missing or corrupted') . ' (1)');
		}
		if (!$userdata = gzuncompress($compressed_data)) {
			die($AppUI->_('The credentials supplied were missing or corrupted') . ' (2)');
		}
		if (!$_REQUEST['check'] = md5($userdata)) {
			die($AppUI->_('The credentials supplied were issing or corrupted') . ' (3)');
		}
		$user_data = unserialize($userdata);

		// Now we need to check if the user already exists, if so we just
		// update.  If not we need to create a new user and add a default
		// role.
		$username = trim($user_data['login']);
		$this->username = $username;
		$names = explode(' ', trim($user_data['name']));
		$last_name = array_pop($names);
		$first_name = implode(' ', $names);
		$passwd = trim($user_data['passwd']);
		$email = trim($user_data['email']);

		$q = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_password, user_contact');
		$q->addWhere('user_username = \'' . $username . '\'');
		if (!$rs = $q->exec()) {
			die($AppUI->_('Failed to get user details') . ' - error was ' . $db->ErrorMsg());
		}
		if ($rs->RecordCount() < 1) {
			$q->clear();
			$this->createsqluser($username, $passwd, $email, $first_name, $last_name);
		} else {
			if (!$row = $rs->FetchRow()) {
				die($AppUI->_('Failed to retrieve user detail'));
			}
			// User exists, update the user details.
			$this->user_id = $row['user_id'];
			$q->clear();
			$q->addTable('users');
			$q->addUpdate('user_password', $passwd);
			$q->addWhere('user_id = ' . $this->user_id);
			if (!$q->exec()) {
				die($AppUI->_('Could not update user credentials'));
			}
			$q->clear();
			$q->addTable('contacts');
			$q->addUpdate('contact_first_name', $first_name);
			$q->addUpdate('contact_last_name', $last_name);
			$q->addUpdate('contact_email', $email);
			$q->addWhere('contact_id = ' . $row['user_contact']);
			if (!$q->exec()) {
				die($AppUI->_('Could not update user details'));
			}
			$q->clear();
		}
		return true;
	}

	public function createsqluser($username, $password, $email, $first, $last) {
		global $db, $AppUI;

		$c = new CContact();
		$c->contact_first_name = $first;
		$c->contact_last_name = $last;
		$c->contact_email = $email;
		$c->contact_order_by = $first . ' ' . $last;

		$q = new DBQuery;
		$q->insertObject('contacts', $c, 'contact_id');
		$q->clear();
		$contact_id = ($c->contact_id == null) ? 'NULL' : $c->contact_id;
		if (!$c->contact_id) {
			die($AppUI->_('Failed to create user details'));
		}

		$q = new DBQuery;
		$q->addTable('users');
		$q->addInsert('user_username', $username);
		$q->addInsert('user_password', $password);
		$q->addInsert('user_type', '1');
		$q->addInsert('user_contact', $c->contact_id);
		if (!$q->exec()) {
			die($AppUI->_('Failed to create user credentials'));
		}
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$q->clear();

		$acl = &$AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}