<?php /* $Id$ $URL$ */

/**
 * @package web2project
 * @subpackage authenticators
 */

class w2p_Authenticators_LDAP extends w2p_Authenticators_SQL {
	public $ldap_host;
	public $ldap_port;
	public $ldap_version;
	public $base_dn;
	public $ldap_search_user;
	public $ldap_search_pass;
	public $filter;

	public $user_id;
	public $username;

	public function __construct() {
		global $w2Pconfig;

		$this->fallback = isset($w2Pconfig['ldap_allow_login']) ? $w2Pconfig['ldap_allow_login'] : false;

		$this->ldap_host = $w2Pconfig['ldap_host'];
		$this->ldap_port = $w2Pconfig['ldap_port'];
		$this->ldap_version = $w2Pconfig['ldap_version'];
		$this->base_dn = $w2Pconfig['ldap_base_dn'];
		$this->ldap_search_user = $w2Pconfig['ldap_search_user'];
		$this->ldap_search_pass = $w2Pconfig['ldap_search_pass'];
		$this->filter = $w2Pconfig['ldap_user_filter'];
	}

	public function authenticate($username, $password) {
		global $w2Pconfig;
		$this->username = $username;

		if (strlen($password) == 0) {
			return false; // LDAP will succeed binding with no password on AD (defaults to anon bind)
		}
		if ($this->fallback == true) {
			if (parent::authenticate($username, $password))
				return true;
		}
		// Fallback SQL authentication fails, proceed with LDAP

		if (!$rs = @ldap_connect($this->ldap_host, $this->ldap_port)) {
			return false;
		}
		@ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
		@ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);

		//$ldap_bind_dn = 'cn='.$this->ldap_search_user.','.$this->base_dn;
		$ldap_bind_dn = empty($this->ldap_search_user) ? null : $this->ldap_search_user;
		$ldap_bind_pw = empty($this->ldap_search_pass) ? null : $this->ldap_search_pass;

		if (!$bindok = @ldap_bind($rs, $ldap_bind_dn, $this->ldap_search_pass)) {
			// Uncomment for LDAP debugging
			/*
			$error_msg = ldap_error($rs);
			die('Couldnt Bind Using '.$ldap_bind_dn.'@'.$this->ldap_host.':'.$this->ldap_port.' Because:'.$error_msg);
			*/
			return false;
		} else {
			$filter_r = html_entity_decode(str_replace('%USERNAME%', $username, $this->filter), ENT_COMPAT, 'UTF-8');
			$result = @ldap_search($rs, $this->base_dn, $filter_r);
			if (!$result) {
				return false; // ldap search returned nothing or error
			}

			$result_user = ldap_get_entries($rs, $result);
			if ($result_user['count'] == 0) {
				return false; // No users match the filter
			}

			$first_user = $result_user[0];
			$ldap_user_dn = $first_user['dn'];

			// Bind with the dn of the user that matched our filter (only one user should match sAMAccountName or uid etc..)

			if (!$bind_user = @ldap_bind($rs, $ldap_user_dn, $password)) {
				/*
				$error_msg = ldap_error($rs);
				die('Couldnt Bind Using '.$ldap_user_dn.'@'.$this->ldap_host.':'.$this->ldap_port.' Because:'.$error_msg);
				*/
				return false;
			} else {
				if ($this->userExists($username)) {
					return true;
				} else {
					$this->createsqluser($username, $password, $first_user);
				}
				return true;
			}
		}
	}

	public function userExists($username) {
		global $db;
		$q = new DBQuery;
		$result = false;
		$q->addTable('users');
		$q->addWhere('user_username = \'' . $username . '\'');
		$rs = $q->exec();
		if ($rs->RecordCount() > 0) {
			$result = true;
		}
		$q->clear();
		return $result;
	}

	public function userId($username) {
		global $db;
		$q = new DBQuery;
		$q->addTable('users');
		$q->addWhere('user_username = \'' . $username . '\'');
		$rs = $q->exec();
		$row = $rs->FetchRow();
		$q->clear();
		return $row['user_id'];
	}

	public function createsqluser($username, $password, $ldap_attribs = array()) {
		global $db, $AppUI;
		$hash_pass = MD5($password);

		if (!count($ldap_attribs) == 0) {
			// Contact information based on the inetOrgPerson class schema
			$c = new CContact();
			$c->contact_first_name = $ldap_attribs['givenname'][0];
			$c->contact_last_name = $ldap_attribs['sn'][0];
			$c->contact_city = $ldap_attribs['l'][0];
			$c->contact_country = $ldap_attribs['country'][0];
			$c->contact_state = $ldap_attribs['st'][0];
			$c->contact_zip = $ldap_attribs['postalcode'][0];
			$c->contact_job = $ldap_attribs['title'][0];
            $c->store();
            $contactArray = array('email_primary' => $ldap_attribs['mail'][0],
                'phone_primary' => $ldap_attribs['telephonenumber'][0],
                'phone_mobile' => $ldap_attribs['mobile'][0]);
            $c->setContactMethods($contactArray);
		}
		$contact_id = ($c->contact_id == null) ? 'NULL' : $c->contact_id;

		$q = new DBQuery;
		$q->addTable('users');
		$q->addInsert('user_username', $username);
		$q->addInsert('user_password', $hash_pass);
		$q->addInsert('user_type', '1');
		$q->addInsert('user_contact', $c->contact_id);
		$q->exec();
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$q->clear();

		$acl = &$AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}